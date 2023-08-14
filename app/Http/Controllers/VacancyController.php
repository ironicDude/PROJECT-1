<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vacancy;


class VacancyController extends Controller
{
    public function index()
    {
    
            $applicant = Vacancy::all();
            return response()->json([
                'Applicant' => $applicant
            ]);
    }
    public function show($id)
    {
    
            $applicant = Vacancy::find($id);
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
        $employee_id = $request->input('employee_id');
        $title = $request->input('title');
        $description = $request->input('description');
        $type = $request->input('type');
        $salary = $request->input('salary');
        $posting_date = $request->input('posting_date');
        $deadline = $request->input('deadline');
        $number_of_vacancies = $request->input('number_of_vacancies');
        $status = $request->input('status');
    
        // إنشاء سجل الطلب في قاعدة البيانات
        $jobPost = new Vacancy();
        $jobPost->employee_id = $employee_id;
        $jobPost->title= $title;
        $jobPost->description = $description;
        $jobPost->type = $type;
        $jobPost->salary = $salary;
        $jobPost->posting_date = $posting_date;
        $jobPost->deadline = $deadline;
        $jobPost->number_of_vacancies = $number_of_vacancies;
        // $jobPost->status = $status;
        $jobPost->save();        
    
        // إعادة استجابة بنجاح
        return response()->json([
            'message' => 'تم انشاء بوست للتوظيف',
            '  $jobPost' =>  $jobPost
        ]);
    }
}
