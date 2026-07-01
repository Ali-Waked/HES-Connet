<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\SubscriptionTypeEnum;
use App\Jobs\SendContentNotificationJob;
use App\Mail\SubscriptionVerificationMail;
use App\Models\Subscription;
use App\Models\SubscriptionType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SubscriptionService
{
    /**
     * Subscribe or reactivate an email address.
     *
     * - If the email has never subscribed: create a new subscription and send verification.
     * - If the email exists but is unverified: update locale, sync types, regenerate token, resend verification.
     * - If the email exists and is already verified: update locale, sync types, reactivate.
     *
     * @param  array{email: string, locale: string, types: string[]}  $data
     */
    public function subscribe(array $data): Subscription
    {
        return DB::transaction(function () use ($data) {
            $subscription = Subscription::where('email', $data['email'])->first();
            $needsVerification = false;

            if ($subscription) {
                $subscription->locale = $data['locale'];

                if ($subscription->verified_at === null) {
                    // Unverified: regenerate token, resend verification
                    $subscription->unsubscribe_token = Str::random(64);
                    $needsVerification = true;
                } else {
                    // Already verified: just reactivate
                    $subscription->is_active = true;
                }

                $subscription->save();
            } else {
                $subscription = Subscription::create([
                    'email' => $data['email'],
                    'locale' => $data['locale'],
                    'is_active' => false,
                    'unsubscribe_token' => Str::random(64),
                ]);
                $needsVerification = true;
            }

            $this->syncTypes($subscription, $data['types']);

            if ($needsVerification) {
                $this->sendVerificationMail($subscription);
            }

            return $subscription->load('subscriptionTypes');
        });
    }

    /**
     * Mark a subscription as verified and active via token.
     */
    public function verify(string $token): Subscription
    {
        $subscription = Subscription::where('unsubscribe_token', $token)->firstOrFail();

        $subscription->update([
            'verified_at' => now(),
            'is_active' => true,
        ]);

        return $subscription->load('subscriptionTypes');
    }

    /**
     * Sync subscription types for a given token.
     *
     * @param  string[]  $types
     */
    public function updateTypes(string $token, array $types): Subscription
    {
        $subscription = Subscription::where('unsubscribe_token', $token)->firstOrFail();

        DB::transaction(function () use ($subscription, $types) {
            $this->syncTypes($subscription, $types);
        });

        return $subscription->load('subscriptionTypes');
    }

    /**
     * Deactivate a subscription without deleting any history.
     */
    public function unsubscribe(string $token): Subscription
    {
        $subscription = Subscription::where('unsubscribe_token', $token)->firstOrFail();

        $subscription->update(['is_active' => false]);

        return $subscription->load('subscriptionTypes');
    }

    /**
     * Notify all active, verified subscribers of a given type about new content.
     *
     * Usage from any module:
     *   SubscriptionService::notifySubscribers('article', $article);
     *   SubscriptionService::notifySubscribers('job', $jobPost);
     *
     * @param  string  $type  One of the SubscriptionTypeEnum values.
     * @param  mixed  $content  The published content model or array.
     */
    public static function notifySubscribers(string $type, mixed $content): void
    {
        Subscription::query()
            ->where('is_active', true)
            ->whereNotNull('verified_at')
            ->whereHas('subscriptionTypes', fn ($q) => $q->where('type', $type))
            ->with('subscriptionTypes')
            ->chunkById(100, function ($subscriptions) use ($type, $content) {
                foreach ($subscriptions as $subscription) {
                    $locale = $subscription->locale;
                    $title = self::extractLocalizedField($content, 'title', $locale);
                    $body = self::extractLocalizedField($content, 'content', $locale)
                                  ?: self::extractLocalizedField($content, 'body', $locale);
                    $contentUrl = self::buildContentUrl($type, $content);

                    dispatch(new SendContentNotificationJob(
                        $subscription,
                        $type,
                        $title,
                        $body,
                        $contentUrl
                    ));
                }
            });
    }

    // -----------------------------------------------------------------------
    // Private helpers
    // -----------------------------------------------------------------------

    /**
     * Replace all subscription types for a subscription.
     *
     * @param  string[]  $types
     */
    private function syncTypes(Subscription $subscription, array $types): void
    {
        $subscription->subscriptionTypes()->delete();

        $rows = array_map(
            fn (string $type) => [
                'subscription_id' => $subscription->id,
                'type' => $type,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            $types
        );

        SubscriptionType::insert($rows);
    }

    /**
     * Build and queue the verification email.
     */
    private function sendVerificationMail(Subscription $subscription): void
    {
        $url = route('subscriptions.verify', ['token' => $subscription->unsubscribe_token]);

        Mail::to($subscription->email)
            ->send(new SubscriptionVerificationMail($subscription, $url));
    }

    /**
     * Extract a localized string field from a content model or array.
     *
     * Supports:
     *  - Spatie translatable models (getTranslation)
     *  - Plain arrays/objects with ['en' => ..., 'ar' => ...] structure
     *  - Plain strings
     */
    private static function extractLocalizedField(mixed $content, string $field, string $locale): string
    {
        if (is_object($content)) {
            if (method_exists($content, 'getTranslation')) {
                try {
                    return (string) ($content->getTranslation($field, $locale, false) ?? '');
                } catch (\Throwable) {
                    // Fall through to property access
                }
            }

            $value = $content->{$field} ?? null;
        } elseif (is_array($content)) {
            $value = $content[$field] ?? null;
        } else {
            return '';
        }

        if (is_array($value)) {
            return (string) ($value[$locale] ?? $value['en'] ?? '');
        }

        return (string) ($value ?? '');
    }

    /**
     * Build the public-facing URL for the published content.
     */
    private static function buildContentUrl(string $type, mixed $content): ?string
    {
        if (! is_object($content)) {
            return null;
        }

        $identifier = $content->uuid ?? $content->slug ?? $content->id ?? null;

        if ($identifier === null) {
            return null;
        }

        $segment = match ($type) {
            SubscriptionTypeEnum::ARTICLE->value => 'articles',
            SubscriptionTypeEnum::STORY->value => 'stories',
            SubscriptionTypeEnum::JOB->value => 'job-posts',
            SubscriptionTypeEnum::EVENT->value => 'events',
            SubscriptionTypeEnum::NEWSLETTER->value => 'newsletters',
            default => Str::plural($type),
        };

        return url("/api/{$segment}/{$identifier}");
    }
}
