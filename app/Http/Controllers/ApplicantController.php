<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Applicant;
use App\Models\User;
use App\Models\Employee;
use App\Mail\ApplicantMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ApplicantController extends Controller
{
    public function index()
{
    header('Access-Control-Allow-Origin: *');
        $applicant = Applicant::all();
        return response()->json([
            'Applicant' => $applicant
        ]);
}
public function show($id)
{
    header('Access-Control-Allow-Origin: *');

        $applicant = Applicant::find($id);
        if ($applicant) {
            # code...
            return response()->json([
                'Applicant' => $applicant
            ]);

        }else {
            return response()->json([
                'Applicant' =>'الموظف غير موجود'
            ]);
        }
}
    public function store(Request $request)
{
    // استقبال بيانات الطلب من النموذج
    $first_name = $request->input('first_name');
    $last_name = $request->input('last_name');
    $email = $request->input('email');
    $mobile = $request->input('mobile');
    $status = $request->input('status');
    $vacancy_type = $request->input('vacancy_type');
    $address = $request->input('address');
    $resume = $request->file('resume');

    // حفظ ملف السيرة الذاتية في مجلد محدد
    $resumePath = $resume->store('resumes');

    // إنشاء سجل الطلب في قاعدة البيانات
    $jobApplication = new Applicant();
    // $jobApplication  = new User();
    $jobApplication->first_name = $first_name;
    $jobApplication->last_name = $last_name;
    $jobApplication->mobile = $mobile;
    $jobApplication->email = $email;
    // $jobApplication->status = $status;
    $jobApplication->vacancy_type = $vacancy_type;
    $jobApplication->address = $address;
    $jobApplication->resume = $resumePath;
    $jobApplication->save();

        // $validatedData = $request->validate([
        //     'first_name' => 'required|string',
        //     'last_name' => 'required|string',
        //     'email' => 'required|email|unique:employee_role,email',
        //     // 'password' => 'required|string',
        //     'address' => 'required|string',
        //     // 'date_of_birth' => 'required|date',
        //     // 'gender' => 'required|string',
        //     'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        //     // 'account_status' => 'required|string',
        //     // 'salary' => 'required|numeric',
        //     // 'personal_email' => 'required|email',
        //     // 'date_of_joining' => 'required|date',
        // ]);

        // // تخزين الصورة إذا تم تحميلها
        // $imagePath = null;
        // if ($request->hasFile('image')) {
        //     $imagePath = $request->file('image')->store('employee_images', 'public');
        // }

        // $first_name = $request->input('first_name');
        // $last_name = $request->input('last_name');
        // $email = $request->input('email');
        // // $password = $request->input('password');
        // $address = $request->input('address');
        // // $date_of_birth = $request->input('date_of_birth');
        // // $gender = $request->input('gender');
        // $image = $request->input('image');
        // // $account_status = $request->input('account_status');
        // // $salary = $request->input('salary');
        // // $personal_email = $request->input('personal_email');
        // // $date_of_joining = $request->input('date_of_joining');

        // $employee = new User();

        // $employee->first_name = $first_name;
        // $employee->last_name = $last_name;
        // $employee->email = $email;
        // // $employee->password = $password;
        // $employee->address = $address;
        // $employee->date_of_birth = $date_of_birth;
        // $employee->gender = $gender;
        // $employee->image = $image;
        // $employee->account_status = $account_status;
        // $employee->salary = $salary;
        // $employee->personal_email = $personal_email;
        // $employee->date_of_joining = $date_of_joining;
        // $employee->save();

        // return response()->json([
        //     'success'=>'تمت إضافة الموظف بنجاح.',
        // ]);

    // إرسال رسالة تأكيد إلى الطالب
        Mail::to($email)->send(new ApplicantMail());


    // إعادة استجابة بنجاح
    return response()->json(['message' => 'تم تقديم طلب التوظيف بنجاح']);
}

public function getFile($id)
{
    $fileData = DB::table('applicants')->where('id', $id)->first();

    if ($fileData) {
        $filePath = $fileData->resume; // افترض أن اسم العمود هو file_path
        $fileContents = Storage::disk('public')->get($filePath);

        return response($fileContents)
            ->header('Content-Type', $fileData->resume)
            ->header('Content-Disposition', 'inline; filename="' . $fileData->resume . '"');
    }

    $employee->delete();

    return response()->json(['success' => 'تم حذف الطلب بنجاح.']);
}

}
