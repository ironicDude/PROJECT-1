<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});




        // Route::post('create', function(){
        // Employee::create([
        //     'first_name' => 'Mo',
        //     'last_name' => 'Mo',
        //     'email' =>'example@j.com',
        //     'password'=>'password',
        //     'address'=>'address',
        //     'date_of_birth'=>'2001-06-06',
        //     'gender_id' =>'1',
        //     'image' =>null,
        //     'salary'=>'34',
        //     'personal_email'=>'hello@persona.com',
        //     'date_of_joining'=>now(),
        //     'role_id'=>'1',
        //     'money' => '89'
        //             ]);
        //         });


