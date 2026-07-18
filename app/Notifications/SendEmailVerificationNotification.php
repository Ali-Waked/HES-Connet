<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class SendEmailVerificationNotification extends VerifyEmail implements ShouldQueue
{
    use Queueable;

    /**
     * Get the verification URL for the given user.
     *
     * URL points to the frontend SPA, which then calls the API to complete verification.
     */
    protected function verificationUrl(User $user): string
    {
        $frontendUrl = Config::get('app.frontend_url', env('FRONTEND_URL', 'http://localhost:4000'));

        $signedUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $user->id,
                'hash' => sha1($user->email),
            ]
        );

        // Replace the backend URL with the frontend URL
        // The frontend route will extract the query params and call the API
        $backendUrl = Config::get('app.url', env('APP_URL', 'http://localhost'));

        return str_replace($backendUrl, $frontendUrl, $signedUrl)
            .'&redirect=/verify-email';
    }

    /**
     * Build the mail representation.
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Verify Your Email Address'))
            ->greeting(__('Hello').' '.$notifiable->name.',')
            ->line(__('Please click the button below to verify your email address.'))
            ->action(__('Verify Email Address'), $this->verificationUrl($notifiable))
            ->line(__('This verification link will expire in :minutes minutes.', ['minutes' => Config::get('auth.verification.expire', 60)]))
            ->line(__('If you did not create an account, no further action is required.'));
    }
}
