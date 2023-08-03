<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use App\Models\UserRole;
use App\Models\Gender;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Validator;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request (Customer Registration).
     *
     * This method is responsible for processing the incoming registration request for creating a new customer account.
     * It first validates the request data using the specified validation rules, ensuring that all required fields are provided
     * and meet the defined criteria. The 'email' field is also checked for uniqueness, verifying that it does not already exist
     * in the 'users' table.
     * If the validation fails, the method returns a JSON response with the validation errors and a status code of 422 (Unprocessable Entity).
     * If the validation passes, the customer's account is created in the 'customers' table using the Customer model's create method.
     * The customer's information, including their name, address, email, gender, date of birth, and password (hashed), is stored in the database.
     * The 'type' field is set to 'customer', and the 'money' field is assigned a random value between 1 and 9999999.
     * After successfully creating the customer's account, a Registered event is triggered to notify listeners.
     * The customer is then automatically logged in using the Auth::login method, providing seamless registration and login.
     * The method returns a JSON response with a success message and a status code of 201 (Created) to indicate successful registration.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request containing the registration data.
     * @return \Illuminate\Http\JsonResponse The JSON response indicating the success of the customer registration.
     * @throws \Illuminate\Validation\ValidationException If the request data fails the validation.
     */
    public function store(Request $request)
    {
        // Validate the incoming request data using the specified validation rules.
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:' . User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'date_of_birth' => 'required|date|before:-10years',
            'gender' => 'required|string|in:Male,Female,I prefer not to say'
        ]);

        // If the validation fails, return a JSON response with the validation errors and status code 422 (Unprocessable Entity).
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Create a new customer account in the 'customers' table using the Customer model's create method.
        $customer = Customer::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'address' => $request->address,
            'email' => $request->email,
            'type' => 'customer',
            'money' => rand(1, 9999999),
            'gender' => $request->gender,
            'password' => Hash::make($request->password),
            'date_of_birth' => $request->date_of_birth
        ]);

        // Trigger the Registered event to notify listeners about the new customer registration.
        event(new Registered($customer));

        // Automatically log in the newly registered customer.
        Auth::login($customer);

        // Return a JSON response with a success message and status code 201 (Created) to indicate successful registration.
        return response()->json(['message' => 'Successfully registered.'], 201);
    }
}
