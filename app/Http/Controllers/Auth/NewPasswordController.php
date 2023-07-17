<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class NewPasswordController extends Controller
{
    /**
     * Handle an incoming new password request (Password Reset).
     *
     * This method is responsible for processing the incoming request to reset a user's password.
     * It first validates the request data to ensure it contains the necessary fields (token, email, password, and password_confirmation),
     * adhering to the rules specified in the validation rules array.
     * If the validation passes, it attempts to reset the user's password using the Password::reset method, provided by Laravel's
     * built-in password reset functionality. If the password reset is successful, the user's password is updated in the database,
     * and a new remember_token is generated to enhance security.
     * If the password reset fails, the method will throw a ValidationException with appropriate error messages based on the status
     * returned by the Password::reset method.
     * After successful password reset, the PasswordReset event is triggered to notify listeners of the password change.
     * The method returns a JSON response containing the status of the password reset operation.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request containing the password reset data.
     * @return \Illuminate\Http\JsonResponse The JSON response indicating the status of the password reset operation.
     * @throws \Illuminate\Validation\ValidationException If the request data fails the validation.
     */
    public function store(Request $request): JsonResponse
    {
        // Validate the incoming request data using the specified validation rules.
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Attempt to reset the user's password using the Password::reset method.
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                // Force-fill the new password and remember_token on the user model.
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                // Trigger the PasswordReset event to notify listeners.
                event(new PasswordReset($user));
            }
        );

        // If the password reset fails, throw a ValidationException with appropriate error messages.
        if ($status != Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        // Return a JSON response with the status of the password reset operation.
        return response()->json(['status' => __($status)]);
    }
}
