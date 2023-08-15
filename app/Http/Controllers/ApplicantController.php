<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Applicant;
use App\Models\Vacancy;
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
public function changeApplicantStatus(Request $request, $id)
{
    $applicant = Applicant::find($id);

    if (!$applicant) {
        return response()->json(['message' => 'المتقدم غير موجود.']);
    }

    $status = $request->status;

    if ($status == 'accepted' || $status == 'rejected') {
        $vacancy = Vacancy::find($applicant->vacancy_type);

        if ($vacancy) {
            if ($status == 'accepted') {
                if ($vacancy->decrementVacancies()) {
                    $applicant->changeStatus('accepted');
                    return response()->json(['message' => 'تم قبول المتقدم.']);
                } else {
                    $vacancy->status = 'غير متاح';
                    $vacancy->save();
                    return response()->json(['message' => 'لا يوجد شواغر متاحة.']);
                }
            } elseif ($status == 'rejected') {
                $applicant->changeStatus('rejected');
                return response()->json(['message' => 'تم رفض المتقدم.']);
            }
        } else {
            return response()->json(['message' => 'الشاغر غير موجود.']);
        }
    } else {
        return response()->json(['message' => 'حالة غير صالحة.']);
    }
}

// public function changeApplicantStatus(Request $request, $id)
// {
//     $applicant = Applicant::find($id);

//     if (!$applicant) {
//         return response()->json(['message' => 'المتقدم غير موجود.']);
//     }

//     $status = $request->status;

//     if ($status == 'accepted' || $status == 'rejected') {
//         $vacancy = Vacancy::find($applicant->vacancy_type);

//         if ($vacancy) {
//             if ($status == 'accepted') {
//                 if ($vacancy->decrementVacancies()) {
//                     $applicant->changeStatus('accepted');

//                     // Create a new user based on the accepted applicant's information
//                     $user = new User();
//                     $user->first_name = $applicant->first_name;
//                     $user->last_name = $applicant->last_name;
//                     $user->address = $applicant->address;
//                     $user->account_status = $applicant->account_status;
//                     $user->email = $applicant->email;
//                     $user->password = bcrypt('your_password_here'); // Set the initial password
//                     $user->save();

//                     return response()->json(['message' => 'تم قبول المتقدم وإنشاء مستخدم جديد.']);
//                 } else {
//                     $vacancy->status = 'غير متاح';
//                     $vacancy->save();
//                     return response()->json(['message' => 'لا يوجد شواغر متاحة.']);
//                 }
//             } elseif ($status == 'rejected') {
//                 $applicant->changeStatus('rejected');
//                 return response()->json(['message' => 'تم رفض المتقدم.']);
//             }
//         } else {
//             return response()->json(['message' => 'الشاغر غير موجود.']);
//         }
//     } else {
//         return response()->json(['message' => 'حالة غير صالحة.']);
//     }
// }

}
