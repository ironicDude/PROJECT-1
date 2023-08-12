<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Applicant;
use App\Mail\ApplicantMail;
use Illuminate\Support\Facades\Mail;

class ApplicantController extends Controller
{
    public function index()
{

        $applicant = Applicant::all();
        return response()->json([
            'Applicant' => $applicant
        ]);
}
public function show($id)
{

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
    $jobApplication->first_name = $first_name;
    $jobApplication->last_name = $last_name;
    $jobApplication->mobile = $mobile;
    $jobApplication->email = $email;
    // $jobApplication->status = $status;
    $jobApplication->vacancy_type = $vacancy_type;
    $jobApplication->address = $address;
    $jobApplication->resume = $resumePath;
    $jobApplication->save();

    // إرسال رسالة تأكيد إلى الطالب
        Mail::to($email)->send(new ApplicantMail());
     

    // إعادة استجابة بنجاح
    return response()->json(['message' => 'تم تقديم طلب التوظيف بنجاح']);
}

}
