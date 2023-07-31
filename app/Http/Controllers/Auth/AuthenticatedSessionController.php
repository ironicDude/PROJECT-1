<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthenticatedSessionController extends Controller
{
    /**
 * Handle an incoming authentication request (User Login).
 *
 * This method handles the incoming authentication request for user login. It receives the login credentials
 * from the LoginRequest object, validates them using the LoginRequest class, and attempts to authenticate the user.
 * If the authentication is successful, the user's session is regenerated to enhance security. A JSON response is
 * returned with a success message, indicating that the user has been logged in, along with additional information
 * about the user (e.g., user type).
 *
 * @param \App\Http\Requests\Auth\LoginRequest $request The incoming HTTP request containing login credentials.
 * @return \Illuminate\Http\Response The JSON response indicating a successful login and additional user information.
 */
public function store(LoginRequest $request)
{

    // $validator = Validator::make($request->all(), [
    //     'email' => 'required|string|email',
    //     'password' => 'required'
    // ]);

    // // If the validation fails, return a JSON response with the validation errors and status code 422 (Unprocessable Entity).
    // if ($validator->fails()) {
    //     return response()->json(['errors' => $validator->errors()], 422);
    // }
    // Attempt to authenticate the user with the provided credentials.
    $request->authenticate();

    // Regenerate the session to enhance security.
    $request->session()->regenerate();

    // Return a JSON response with a success message and additional user information (e.g., user type).
    return response()->json([
        'message' => 'User has been logged in',
        'type' => Auth::user()->type,
        'info' => new UserResource(Auth::user()),
    ], 200);
}

/**
 * Destroy an authenticated session (User Logout).
 *
 * This method handles the destruction of an authenticated user session, effectively logging the user out.
 * It logs the user out using the 'web' guard and invalidates the session. Additionally, a new CSRF token
 * is regenerated to prevent any potential cross-site request forgery attacks. A JSON response is returned
 * with a success message, indicating that the user has been logged out.
 *
 * @param \Illuminate\Http\Request $request The incoming HTTP request.
 * @return \Illuminate\Http\Response The JSON response indicating a successful logout.
 */
public function destroy(Request $request)
{
    // Log the user out using the 'web' guard.
    Auth::guard('web')->logout();

    // Invalidate the session.
    $request->session()->invalidate();

    // Regenerate a new CSRF token to enhance security.
    $request->session()->regenerateToken();

    // Return a JSON response with a success message indicating that the user has been logged out.
    return response()->json([
        'message' => 'User has been logged out'
    ], 200);
}

}
