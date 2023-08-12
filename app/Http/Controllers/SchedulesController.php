<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Schedules;

class SchedulesController extends Controller
{
    public function store(Request $request, $employeeId)
    {
        $employee = User::findOrFail($employeeId);

        $validatedData = $request->validate([
            'employee_id' => 'required',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'day' => 'required|string|max:20',
        ]);

        $schedules = new Schedules([
            'employee_id' => $request->input('employee_id'),
            'start_time' => $request->input('start_time'),
            'end_time' => $request->input('end_time'),
            'day' => $request->input('day'),
        ]);

        $employee->roles()->save($schedules);

        return response()->json([
            'message'=>'ok'
        ]);
    }
}
