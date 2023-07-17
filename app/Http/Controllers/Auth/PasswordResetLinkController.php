<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class PasswordResetLinkController extends Controller
{
    /**
 * Handle an incoming password reset link request (Send Reset Link).
 *
 * This method is responsible for processing the incoming request to send a password reset link to a user.
 * It first validates the request data to ensure it contains the necessary field (email), adhering to the rules specified in the validation rules array.
 * If the validation passes, it attempts to send the password reset link to the user using the Password::sendResetLink method,
 * provided by Laravel's built-in password reset functionality.
 * The method examines the response from the sendResetLink method to determine the status of the link sending operation.
 * If the link sending is successful, the method returns a JSON response with a success status (RESET_LINK_SENT).
 * If the link sending fails, the method will throw a ValidationException with appropriate error messages based on the status returned by the sendResetLink method.
 * The JSON response contains the status of the link sending operation.
 *
 * @param \Illuminate\Http\Request $request The incoming HTTP request containing the user's email for the password reset.
 * @return \Illuminate\Http\JsonResponse The JSON response indicating the status of the password reset link sending operation.
 * @throws \Illuminate\Validation\ValidationException If the request data fails the validation.
 */
public function store(Request $request): JsonResponse
{
    // Validate the incoming request data using the specified validation rules.
    $request->validate([
        'email' => ['required', 'email'],
    ]);

    // Attempt to send the password reset link to the user using the Password::sendResetLink method.
    $status = Password::sendResetLink(
        $request->only('email')
    );

    // If the password reset link sending fails, throw a ValidationException with appropriate error messages.
    if ($status != Password::RESET_LINK_SENT) {
        throw ValidationException::withMessages([
            'email' => [__($status)],
        ]);
    }

    // Return a JSON response with the status of the password reset link sending operation.
    return response()->json(['status' => __($status)]);
}

}
