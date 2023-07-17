<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     *
     * This method is responsible for sending a new email verification notification to the user.
     * First, it checks if the user's email has already been verified using the 'hasVerifiedEmail' method.
     * If the email is already verified, it returns a JSON response with a conflict status (HTTP 409) and a message indicating
     * that the email is already verified.
     * Otherwise, it proceeds to send the email verification notification to the user using the 'sendEmailVerificationNotification'
     * method provided by the User model, which generates and sends the verification link to the user's email address.
     * After successfully sending the notification, a JSON response is returned with a success message indicating that the
     * verification link has been sent.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request containing the authenticated user.
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse The JSON response or a redirect response.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        // Check if the user's email is already verified.
        if ($request->user()->hasVerifiedEmail()) {
            // Return a JSON response indicating that the email is already verified.
            return response()->json(['message' => 'Email already verified'], 409); // HTTP 409: Conflict.
        }

        // Send the email verification notification to the user.
        $request->user()->sendEmailVerificationNotification();

        // Return a JSON response indicating that the verification link has been sent successfully.
        return response()->json(['message' => 'Verification link sent'], 200);
    }
}
