<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\SendContentNotificationJob;
use App\Mail\ContentPublishedMail;
use App\Mail\SubscriptionVerificationMail;
use App\Models\Subscription;
use App\Models\SubscriptionType;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    // -----------------------------------------------------------------------
    // Subscribe
    // -----------------------------------------------------------------------

    /** @test */
    public function it_creates_a_new_subscription_and_sends_verification_email(): void
    {
        Mail::fake();

        $response = $this->postJson('/api/public/subscriptions', [
            'email' => 'test@example.com',
            'locale' => 'en',
            'types' => ['article', 'job'],
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.email', 'test@example.com')
            ->assertJsonPath('data.locale', 'en')
            ->assertJsonPath('data.is_active', false)
            ->assertJsonStructure([
                'success', 'message',
                'data' => ['unsubscribe_token', 'email', 'locale', 'is_active', 'verified_at', 'types'],
            ]);

        $this->assertDatabaseHas('subscriptions', [
            'email' => 'test@example.com',
            'locale' => 'en',
            'is_active' => false,
        ]);

        $this->assertDatabaseHas('subscription_types', ['type' => 'article']);
        $this->assertDatabaseHas('subscription_types', ['type' => 'job']);

        Mail::assertSent(SubscriptionVerificationMail::class, function (SubscriptionVerificationMail $mail) {
            return $mail->hasTo('test@example.com')
                && $mail->subscription->email === 'test@example.com';
        });
    }

    /** @test */
    public function it_reactivates_unverified_subscription_and_regenerates_token(): void
    {
        Mail::fake();

        $subscription = Subscription::create([
            'email' => 'test@example.com',
            'locale' => 'ar',
            'is_active' => false,
            'unsubscribe_token' => 'old-token-string',
        ]);
        SubscriptionType::create(['subscription_id' => $subscription->id, 'type' => 'article']);

        $response = $this->postJson('/api/public/subscriptions', [
            'email' => 'test@example.com',
            'locale' => 'en',
            'types' => ['story'],
        ]);

        $response->assertStatus(201);

        $subscription->refresh();
        $this->assertEquals('en', $subscription->locale);
        $this->assertNotEquals('old-token-string', $subscription->unsubscribe_token);
        $this->assertDatabaseHas('subscription_types', ['subscription_id' => $subscription->id, 'type' => 'story']);
        $this->assertDatabaseMissing('subscription_types', ['subscription_id' => $subscription->id, 'type' => 'article']);

        Mail::assertSent(SubscriptionVerificationMail::class);
    }

    /** @test */
    public function it_reactivates_verified_subscription_without_resending_verification(): void
    {
        Mail::fake();

        $subscription = Subscription::create([
            'email' => 'test@example.com',
            'locale' => 'en',
            'is_active' => false,
            'verified_at' => now(),
            'unsubscribe_token' => 'existing-token',
        ]);

        $response = $this->postJson('/api/public/subscriptions', [
            'email' => 'test@example.com',
            'locale' => 'ar',
            'types' => ['event'],
        ]);

        $response->assertStatus(201);

        $subscription->refresh();
        $this->assertEquals('ar', $subscription->locale);
        $this->assertTrue($subscription->is_active);
        $this->assertEquals('existing-token', $subscription->unsubscribe_token); // Token unchanged

        Mail::assertNotSent(SubscriptionVerificationMail::class);
    }

    /** @test */
    public function it_rejects_invalid_subscription_types(): void
    {
        $response = $this->postJson('/api/public/subscriptions', [
            'email' => 'test@example.com',
            'locale' => 'en',
            'types' => ['invalid_type'],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['types.0']);
    }

    /** @test */
    public function it_rejects_invalid_locale(): void
    {
        $response = $this->postJson('/api/public/subscriptions', [
            'email' => 'test@example.com',
            'locale' => 'fr',
            'types' => ['article'],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['locale']);
    }

    // -----------------------------------------------------------------------
    // Verify
    // -----------------------------------------------------------------------

    /** @test */
    public function it_verifies_subscription_and_activates_it(): void
    {
        $subscription = Subscription::create([
            'email' => 'test@example.com',
            'locale' => 'en',
            'is_active' => false,
            'unsubscribe_token' => 'verify-me-token',
        ]);

        $response = $this->getJson('/api/public/subscriptions/verify/verify-me-token');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.is_active', true);

        $subscription->refresh();
        $this->assertTrue($subscription->is_active);
        $this->assertNotNull($subscription->verified_at);
    }

    /** @test */
    public function it_returns_404_for_invalid_verify_token(): void
    {
        $response = $this->getJson('/api/public/subscriptions/verify/non-existent-token');

        $response->assertStatus(404);
    }

    // -----------------------------------------------------------------------
    // Update Types
    // -----------------------------------------------------------------------

    /** @test */
    public function it_syncs_subscription_types_via_token(): void
    {
        $subscription = Subscription::create([
            'email' => 'test@example.com',
            'locale' => 'en',
            'is_active' => true,
            'verified_at' => now(),
            'unsubscribe_token' => 'update-token',
        ]);
        SubscriptionType::create(['subscription_id' => $subscription->id, 'type' => 'article']);

        $response = $this->patchJson('/api/public/subscriptions/update-token', [
            'types' => ['job', 'story'],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('subscription_types', ['subscription_id' => $subscription->id, 'type' => 'job']);
        $this->assertDatabaseHas('subscription_types', ['subscription_id' => $subscription->id, 'type' => 'story']);
        $this->assertDatabaseMissing('subscription_types', ['subscription_id' => $subscription->id, 'type' => 'article']);
    }

    /** @test */
    public function it_returns_404_for_invalid_update_token(): void
    {
        $response = $this->patchJson('/api/public/subscriptions/no-such-token', [
            'types' => ['article'],
        ]);

        $response->assertStatus(404);
    }

    // -----------------------------------------------------------------------
    // Unsubscribe
    // -----------------------------------------------------------------------

    /** @test */
    public function it_unsubscribes_and_keeps_record(): void
    {
        $subscription = Subscription::create([
            'email' => 'test@example.com',
            'locale' => 'en',
            'is_active' => true,
            'verified_at' => now(),
            'unsubscribe_token' => 'unsub-token',
        ]);

        $response = $this->postJson('/api/public/subscriptions/unsubscribe/unsub-token');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.is_active', false);

        // Record is NOT deleted
        $this->assertDatabaseHas('subscriptions', ['id' => $subscription->id, 'is_active' => false]);
    }

    /** @test */
    public function it_returns_404_for_invalid_unsubscribe_token(): void
    {
        $response = $this->postJson('/api/public/subscriptions/unsubscribe/ghost-token');

        $response->assertStatus(404);
    }

    // -----------------------------------------------------------------------
    // Notify Subscribers
    // -----------------------------------------------------------------------

    /** @test */
    public function it_dispatches_jobs_only_to_active_verified_subscribers_of_matching_type(): void
    {
        Queue::fake();

        // Should receive: active + verified + correct type
        $sub1 = Subscription::create(['email' => 'active@example.com', 'locale' => 'en', 'is_active' => true, 'verified_at' => now(), 'unsubscribe_token' => 'tok1']);
        SubscriptionType::create(['subscription_id' => $sub1->id, 'type' => 'article']);

        // Should skip: inactive
        $sub2 = Subscription::create(['email' => 'inactive@example.com', 'locale' => 'en', 'is_active' => false, 'verified_at' => now(), 'unsubscribe_token' => 'tok2']);
        SubscriptionType::create(['subscription_id' => $sub2->id, 'type' => 'article']);

        // Should skip: unverified
        $sub3 = Subscription::create(['email' => 'unverified@example.com', 'locale' => 'en', 'is_active' => true, 'verified_at' => null, 'unsubscribe_token' => 'tok3']);
        SubscriptionType::create(['subscription_id' => $sub3->id, 'type' => 'article']);

        // Should skip: wrong type
        $sub4 = Subscription::create(['email' => 'jobonly@example.com', 'locale' => 'en', 'is_active' => true, 'verified_at' => now(), 'unsubscribe_token' => 'tok4']);
        SubscriptionType::create(['subscription_id' => $sub4->id, 'type' => 'job']);

        $content = ['title' => ['en' => 'Test Title', 'ar' => 'عنوان'], 'content' => ['en' => 'Body text', 'ar' => 'نص']];

        SubscriptionService::notifySubscribers('article', $content);

        Queue::assertPushed(SendContentNotificationJob::class, 1);
        Queue::assertPushed(SendContentNotificationJob::class, fn ($job) => $job->subscription->id === $sub1->id);
    }

    /** @test */
    public function it_sends_localized_email_to_arabic_subscriber(): void
    {
        Mail::fake();

        $subscription = Subscription::create([
            'email' => 'arabic@example.com',
            'locale' => 'ar',
            'is_active' => true,
            'verified_at' => now(),
            'unsubscribe_token' => 'arabic-token',
        ]);

        $job = new SendContentNotificationJob(
            $subscription,
            'article',
            'عنوان المقال',
            'محتوى المقال',
            'http://localhost/api/articles/123'
        );
        $job->handle();

        Mail::assertSent(ContentPublishedMail::class, function (ContentPublishedMail $mail) {
            return $mail->hasTo('arabic@example.com')
                && $mail->title === 'عنوان المقال'
                && $mail->body === 'محتوى المقال'
                && $mail->type === 'article';
        });
    }

    /** @test */
    public function it_does_not_delete_data_on_unsubscribe(): void
    {
        $subscription = Subscription::create([
            'email' => 'keep@example.com',
            'locale' => 'en',
            'is_active' => true,
            'verified_at' => now(),
            'unsubscribe_token' => 'keep-token',
        ]);
        SubscriptionType::create(['subscription_id' => $subscription->id, 'type' => 'newsletter']);

        $this->postJson('/api/public/subscriptions/unsubscribe/keep-token');

        // Row still in DB
        $this->assertDatabaseHas('subscriptions', ['id' => $subscription->id]);
        // Types still in DB
        $this->assertDatabaseHas('subscription_types', ['subscription_id' => $subscription->id, 'type' => 'newsletter']);
    }
}
