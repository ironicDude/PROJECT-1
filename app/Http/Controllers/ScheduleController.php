<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Schedule;
use App\Mail\WorkingMail;
use Illuminate\Support\Facades\Mail;
class ScheduleController extends Controller
{
    public function index()
    {
        $schedules = Schedule::all();
        return response()->json($schedules);
    }

        public function show($id)
        {
            $employee = Schedule::find($id);
    
            if (!$employee) {
                return response()->json(['error' => 'الموظف غير موجود.'], 404);
            }
    
            return response()->json($employee);
        }
    
    
    public function store(Request $request)
    {
        $request->validate([
            'scheduler_id' => 'required|exists:users,id', // الموظف الذي يقوم بتعيين الدوام
            'employee_id' => 'required|exists:users,id', // الموظف الذي سيتم تعيين أوقات الدوام له
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'day' => 'required|string|max:20',
        ]);

        $scheduler = User::find($request->input('scheduler_id'));
        $employee = User::find($request->input('employee_id'));

        if ($scheduler && $employee) {
            $schedule = new Schedule([
                'scheduler_id' => $request->input('scheduler_id'),
                'start_time' => $request->input('start_time'),
                'end_time' => $request->input('end_time'),
                'day' => $request->input('day'),
            ]);

            $employee->schedules()->save($schedule);

            return response()->json(['message' => 'تم تعيين جدول الدوام بنجاح.']);
        } else {
            return response()->json(['message' => 'الموظف الذي يقوم بتعيين الدوام أو الموظف غير موجود.'], 404);
        }
    }
    public function update(Request $request, $id)
{
    $request->validate([
        'scheduler_id' => 'required|exists:users,id',
        'employee_id' => 'required|exists:users,id',
        'start_time' => 'required|date_format:H:i',
        'end_time' => 'required|date_format:H:i',
        'day' => 'required|string|max:20',
    ]);

    $scheduler = User::find($request->input('scheduler_id'));
    $employee = User::find($request->input('employee_id'));

    if ($scheduler && $employee) {
        $schedule = Schedule::find($id);

        if (!$schedule) {
            return response()->json(['message' => 'جدول الدوام غير موجود.'], 404);
        }

        $schedule->scheduler_id = $request->input('scheduler_id');
        $schedule->start_time = $request->input('start_time');
        $schedule->end_time = $request->input('end_time');
        $schedule->day = $request->input('day');

        $schedule->save();

        $emailData = [
            'employee_name' => $schedule->start_time . ' ' . $schedule->end_time. ' ' . $schedule->day
            // ... تضمين المزيد من البيانات التي تحتاجها في البريد
        ];
        return response()->json(['message' => 'تم تحديث جدول الدوام بنجاح.']);
        Mail::to($email)->send(new WorkingMail( $emailData));
    } else {
        return response()->json(['message' => 'الموظف الذي يقوم بالتعديل أو الموظف غير موجود.'], 404);
    }
}
// تعيين نفس اوقات دوام لعدة موظفين

    public function updateMultipleSchedules(Request $request)
    {
        $employeeIds = $request->input('employee_ids');
        $start_time = $request->input('start_time');
        $end_time = $request->input('end_time');
        $day = $request->input('day');

        foreach ($employeeIds as $employeeId) {
            $schedule = Schedule::where('employee_id', $employeeId)->first();

            if ($schedule) {
                $schedule->start_time = $start_time;
                $schedule->end_time = $end_time;
                $schedule->day = $day;
                $schedule->save();
            }
        }

        return response()->json(['success' => 'تم تحديث أوقات الدوام للموظفين بنجاح.']);
    }

public function destroy($id)
{
    $schedule = Schedule::find($id);

    if (!$schedule) {
        return response()->json(['message' => 'جدول الدوام غير موجود.'], 404);
    }

    $schedule->delete();

    return response()->json(['message' => 'تم حذف جدول الدوام بنجاح.']);
}


}
