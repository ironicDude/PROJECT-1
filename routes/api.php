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
use App\Http\Controllers\User\EmployeeController;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\User;
use App\Models\Order;
use App\Models\Schedule;
use App\Http\Controllers\PresciptionController;
use App\Http\Controllers\Order\OrderController;
use App\Http\Controllers\Order\Orderd_ProductsController;
use App\Http\Controllers\ApplicantController;
use App\Http\Controllers\VacancyController;
use App\Http\Controllers\ApplicationController;
// use App\Http\Controllers\SchedulesController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ScheduleController;
use App\Mail\StatusMail;
use Illuminate\Support\Facades\Mail;


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

Route::resource('order',OrderController::class);
Route::resource('prescription',PresciptionController::class);
Route::get('getImage/{id}',[PresciptionController::class,'getImage']);
Route::post('update/{id}',[PresciptionController::class,'update']);
Route::post('/send-email-to-customer/{id}', function ($id, Request $request) {
    $customer = Customer::find($id);

    if (!$customer) {
        return "الزبون غير موجود.";
    }
    $subject = $request->input('subject');
    $message = $request->input('message');

    if (!$subject || !$message) {
        return "الرجاء تقديم عنوان ورسالة صحيحة.";
    }
    $message .=  " مرحبًا " . $customer->name . "، \n\n";
    $message .="\nشكرًا لاختيارك خدماتنا.\n ";
    $message .= " \n\nمع أطيب التحيات،\nفريق الدعم الخاص بنا ";

    Mail::raw($message, function ($mail) use ($customer, $subject) {
        $mail->to($customer->email);
        $mail->subject($subject);
    });

    return "تم إرسال رسالة البريد الإلكتروني بنجاح إلى الزبون.";
});
Route::resource('/applicant',ApplicantController::class);
Route::resource('/employee',EmployeeController::class);
Route::resource('/vacancy',VacancyController::class);
// Route::resource('/schedules/{id}',SchedulesController::class);
// Route::resource('/acceptEmployeeForVacancy',[ Applicant_VacancyController::class,'acceptEmployeeForVacancy']);
Route::get('/getApplicantsForVacancy/{id}',[ ApplicationController::class,'getApplicantsForVacancy']);
Route::get('/getFile/{id}',[ ApplicantController::class,'getFile']);
Route::post('/acceptApplicant/{applicantId}/{vacancyId}',[ ApplicationController::class,'acceptApplicant']);
Route::post('/applytojob',[ ApplicationController::class,'applytojob']);
Route::post('/storeapplicantwithvacancyid/{id}',[ ApplicationController::class,'storeapplicantwithvacancyid']);//تقديم طلب توظيف لشاغر معين
Route::get('/getapplicanttovacancy/{id}',[ ApplicationController::class,'getapplicanttovacancy']);// جلب جميع المتقدمين لوظيفة معينة
Route::post('/changeApplicantStatus/{id}',[ ApplicationController::class,'changeApplicantStatus']);// قبول او رفض متقدم لوظيفة
// Route::post('/addEmployee',[ EmployeeController::class,'addEmployee']);// اظافة موظف جديد
Route::resource('/employee', EmployeeController::class);//  اظافة موظف جديد وتعديل بياناته وحذف موظف
Route::post('/assignRole',[ RoleController::class,'assignRole']);// تحديد ادوار للموظفين
Route::resource('/schedule',ScheduleController::class);// تحديد اوقات الدوام وتعديلها وحذفها
Route::post('/updateMultipleSchedules',[ScheduleController::class,'updateMultipleSchedules']);// تعيين نفس اوقات دوام لعدة موظفين
Route::resource('/orderd_product',Orderd_ProductsController::class);// تحديد اوقات الدوام وتعديلها وحذفها
Route::post('/create_orderd_product/{id}',[Orderd_ProductsController::class,'create_orderd_product']);// تحديد اوقات الدوام وتعديلها وحذفها
Route::put('/update_orderd_product/{id}',[Orderd_ProductsController::class,'update_orderd_product']);// تحديد اوقات الدوام وتعديلها وحذفها
Route::delete('/deleteByDatedProductId/{id}',[Orderd_ProductsController::class,'deleteByDatedProductId']);// تحديد اوقات الدوام وتعديلها وحذفها





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


