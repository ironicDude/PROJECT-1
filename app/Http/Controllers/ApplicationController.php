<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vacancy;
use App\Models\Applicant;
use App\Mail\ApplicantMail;
use App\Mail\RejectMail;
use App\Mail\AcceptMail;
use App\Models\Application;
use Illuminate\Support\Facades\Mail;

class ApplicationController extends Controller
{

public function getApplicantsForVacancy($vacancyId)
{
    // $vacancy = Vacancy::findOrFail($vacancyId);
    $applicants = Application::where('vacancy_id', $vacancyId)->get();
    // $applicants = $vacancy->applicants;

    return response()->json([
                'Applicants' =>  $applicants
            ]);
}

// public function applyForVacancy($applicantId, $vacancyId)
// {
//     $applicant = Applicant::find($applicantId);
//     $vacancy = Vacancy::find($vacancyId);

//     if (!$applicant || !$vacancy) {
//         return "المتقدم أو الشاغر غير موجود.";
//     }

//     $applicant->vacancies()->attach($vacancyId, [
//         'dateTime' => now(),
//         'status' => 'Pending',
//     ]);

//     return "تم تقديم طلبك بنجاح.";
// }
public function acceptApplicant($vacancyId, $applicantId)
{
    $vacancy = Vacancy::find($vacancyId);

    if (!$vacancy) {
        return "الشاغر غير موجود.";
    }

    $applicant = Applicant::find($applicantId);

    if (!$applicant) {
        return "المتقدم غير موجود.";
    }

    return response()->json([
        'vacancy'=> $vacancy,
        'applicant'=>  $applicant,

    ]);
    // $vacancy->applicants()->updateExistingPivot($applicantId, ['status' => 'مقبول']);

    // return "تم قبول المتقدم بنجاح.";
}
public function applytojob(Request $request)
{
    $first_name = $request->input('first_name');
    $last_name = $request->input('last_name');
    $email = $request->input('email');
    $mobile = $request->input('mobile');
    $status = $request->input('status');
    $address = $request->input('address');
    $resume = $request->file('resume');
    $applicant_id = $request->input('applicant_id');
    $vacancy_id  =  $request->input('vacancy_id ');
    $vacancy_id  =  $request->input('vacancy_id ');


    // حفظ ملف السيرة الذاتية في مجلد محدد
    $resumePath = $resume->store('resumes');

    // إنشاء سجل الطلب في قاعدة البيانات
    $jobApplication = new Applicant();
    $jobApplication->first_name = $first_name;
    $jobApplication->last_name = $last_name;
    $jobApplication->mobile = $mobile;
    $jobApplication->email = $email;
    $jobApplication->status = $status;
    $jobApplication->address = $address;
    $jobApplication->resume = $resumePath;
    $jobApplication->save();
    $applicant_vacancy = new Application();
    $applicant_vacancy->applicant_id = $applicant_id ;
    $applicant_vacancy->vacancy_id  = $vacancy_id ;
    $applicant_vacancy->save();

    // $applicant_id = $applicantId;
    // $vanacy_id = $vacancyId;

    // $vacancy = Vacancy::findOrFail($vacancyId);
    // $applicant = Applicant::findOrFail($applicantId);


    // قم بتحديث حقل العمود الخاص بموظف الشاغر المقبول في جدول الشواغر
    // قم بإرجاع رسالة استجابة توضح نجاح عملية القبول
    return response()->json([
        'message' => 'تم قبول الموظف بنجاح في الشاغر.'
    ]);
}

// تقديم طلب توظيف لشاغر معين
public function storeapplicantwithvacancyid(Request $request,$vacancyId)
{
    $vacancy_id = $vacancyId;
    $vacancy = Vacancy::find($vacancyId);
    // استقبال بيانات الطلب من النموذج
    // if ($vacancy->number_of_vacancies > 0) {
    $first_name = $request->input('first_name');
    $last_name = $request->input('last_name');
    $email = $request->input('email');
    $mobile = $request->input('mobile');
    $vacancy_type = $vacancy_id;
    $status = $request->input('status');
    $address = $request->input('address');
    $resume = $request->file('resume');

    // حفظ ملف السيرة الذاتية في مجلد محدد
    $resumePath = $resume->store('resumes');

    // إنشاء سجل الطلب في قاعدة البيانات
    $jobApplication = new Applicant();
    $jobApplication->first_name = $first_name;
    $jobApplication->last_name = $last_name;
    $jobApplication->email = $email;
    $jobApplication->mobile = $mobile;
    $jobApplication->vacancy_type = $vacancy_type;
    // $jobApplication->status = $status;
    $jobApplication->address = $address;
    $jobApplication->resume = $resumePath;
    $jobApplication->save();
    // تقليل عدد الشواغر
    // $vacancy->number_of_vacancies = $vacancy->number_of_vacancies - 1;
    // $vacancy->save();
    // إرسال رسالة تأكيد إلى الطالب
        // Mail::to($email)->send(new ApplicantMail());


    // إعادة استجابة بنجاح
    return response()->json(['message' => 'تم تقديم طلب التوظيف بنجاح']);
// }else {
    //     return response()->json([
    //         'message' =>  "تم رفض طلب التوظيف."
    //     ]);
    // }

}

// جلب جميع المتقدمين لوظيفة معينة
public function getapplicanttovacancy($vacancyType){
    // $vacancy_type = $id;
    // $vacancy = Applicant::find($vacancy_type);
    $applicants = Applicant::where('vacancy_type', $vacancyType)->get();

    if (  $applicants ) {
        # code...
        return response()->json(['   $vacancy ' =>$applicants ]);
    }else {
        return response()->json(['message' => 'لم يتقدم اي شخص لهذه الوظيفة']);

    }

}
// قبول او رفض متقدم لوظيفة
public function changeApplicantStatus(Request $request,int $id)
{
    // $applicantId = $request->applicant_id;
    $applicantId = $id;
    $status = $request->status;

    $applicant = Applicant::find($applicantId);
    $vacancy_type = $applicant->vacancy_type;
    // $vacancy_type = Applicant::where('vacancy_type', $vacancy_type)->get();

    $vacancy_id = Vacancy::find($vacancy_type);
    // $va=$vacancy_id->$id;
    // $vacancy = Vacancy::find($vacancyId);

    if ($applicant) {
        if ($status == 'accepted') {
                if ( $vacancy_id->number_of_vacancies > 0)
                {
                $applicant->status = 'مقبول';
                $applicant->save();
                $vacancy_id->number_of_vacancies = $vacancy_id->number_of_vacancies - 1;
                $vacancy_id->save();
                // $vacancy_id->save();

                // Mail::to($applicant->email)->send(new AcceptMail());
                return response()->json([
                    'vacancy_type'=> $vacancy_type,
                    'vacancy_id'=> $vacancy_id,
                    'message' => 'accepted',
                ])->header('Access-Control-Allow-Origin', 'http://localhost:3000
                ');
            }else {
                $vacancy_id->status = 'غير متاح';
                $vacancy_id->save();
                return response()->json(['لا يوجد شواغر عذرا .'])->header('Access-Control-Allow-Origin', 'http://localhost:3000
                ');
               }

            } elseif ($status == 'rejected') {
                $applicant->status = 'مرفوض';
                $applicant->save();
                // Mail::to($applicant->email)->send(new RejectMail());
                return response()->json(['تم رفض المتقدم .'])->header('Access-Control-Allow-Origin', 'http://localhost:3000
                ');
        } else {
            return response()->json(['حالة غير صالحة.'])->header('Access-Control-Allow-Origin', 'http://localhost:3000
            ');
        }


    } else {
        return response()->json(['المتقدم غير موجود.'])->header('Access-Control-Allow-Origin', 'http://localhost:3000
        ');
    }
}


}
