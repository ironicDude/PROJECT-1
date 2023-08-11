<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     *
     * This method is called when the user clicks the email verification link and handles marking the user's email address
     * as verified in the database.
     * First, it checks if the user's email has already been verified using the 'hasVerifiedEmail' method.
     * If the email is already verified, it returns a JSON response with a conflict status (HTTP 409) and a message indicating
     * that the email is already verified.
     * If the email is not verified, it calls the 'markEmailAsVerified' method on the user model to mark the email as verified
     * in the database. The 'markEmailAsVerified' method sets the 'email_verified_at' column to the current timestamp, indicating
     * that the email has been verified.
     * If the email is successfully marked as verified, the 'Verified' event is triggered to notify listeners.
     * The method returns a JSON response with a success message indicating that the email has been verified.
     *
     * @param \Illuminate\Foundation\Auth\EmailVerificationRequest $request The incoming HTTP request containing the email verification token.
     * @return \Illuminate\Http\JsonResponse The JSON response indicating the success of the email verification.
     */
    public function __invoke(EmailVerificationRequest $request)
    {
        // Check if the user's email is already verified.
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(env('FRONTEND_URL').'?verified=1');
        }

        // Mark the user's email as verified in the database.
        if ($request->user()->markEmailAsVerified()) {
            // Trigger the Verified event to notify listeners about the email verification.
            event(new Verified($request->user()));
        }

        // Return a JSON response indicating that the email has been verified.
        return redirect()->intended(env('FRONTEND_URL').'?verified=1');
    }
}
