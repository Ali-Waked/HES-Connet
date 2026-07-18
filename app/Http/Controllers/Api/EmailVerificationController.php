<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class EmailVerificationController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     *
     * GET /api/email/verify/{id}/{hash}
     */
    public function verify(Request $request, int $id, string $hash): JsonResponse
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json([
                'message' => __('Invalid verification link.'),
            ], 404);
        }

        // Check if the hash matches
        if (sha1($user->email) !== $hash) {
            return response()->json([
                'message' => __('Invalid verification link.'),
            ], 403);
        }

        // Check if the signature is valid (query parameter)
        if (! URL::hasValidSignature($request)) {
            return response()->json([
                'message' => __('This verification link has expired. Please request a new one.'),
            ], 403);
        }

        // Mark as verified
        if ($user->email_verified_at === null) {
            $user->email_verified_at = now();
            $user->save();

            event(new Verified($user));
        }

        return response()->json([
            'message' => __('Email verified successfully.'),
        ]);
    }

    /**
     * Resend the email verification notification.
     *
     * POST /api/email/verification-notification
     */
    public function resend(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->email_verified_at !== null) {
            return response()->json([
                'message' => __('Email is already verified.'),
            ]);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => __('Verification email sent. Please check your inbox.'),
        ]);
    }
}
