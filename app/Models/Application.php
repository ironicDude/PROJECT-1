<?php

namespace App\Models;

use App\Exceptions\ApplicationAlreadyAcceptedException;
use App\Exceptions\ApplicationAlreadyRejectedException;
use App\Mail\AcceptMail;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
class Application extends Model
{
    protected $fillable = [
        'status',
        'number_of_vacancies',
        'applicant_id',
        'vacancy_id',
    ];

    protected function validateStatus()
    {
        if($this->status == 'Accepted') {
            throw new ApplicationAlreadyAcceptedException('This application has already been accepted');
        } elseif($this->status == 'Rejected') {
            throw new ApplicationAlreadyRejectedException('This application has already been rejected');
        }
    }
    public function accept()
    {
        $this->validateStatus();
        $vacancy = $this->vacancy;
        $vacancy->decrement('number_of_vacancies', 1);

        $applicant = $this->applicant;
        $password = Str::password();
        $employee = Employee::create([
            'first_name' => $applicant->first_name,
            'last_name' => $applicant->last_name,
            'email' => $applicant->generateWorkEmail(),
            'password' => Hash::make($password),
            'address' => $applicant->address,
            'date_of_birth' => $applicant->date_of_birth,
            'gender' => $applicant->gender,
            'salary' => $vacancy->salary,
            'personal_email' => $applicant->email,
            'date_of_joining' => Carbon::now(),
        ]);

        $this->markAsAccepted();

        Mail::to($applicant)->send(new AcceptMail($employee, $password));
    }

    public function reject()
    {
        $this->validateStatus();
        $this->markAsRejected();
    }

    protected function markAsRejected()
    {
        $this->status = 'Rejected';
        $this->save();
    }
    protected function markAsAccepted()
    {
        $this->status = 'Accepted';
        $this->save();
    }
    public function applicant()
    {
        return $this->belongsTo(Applicant::class, 'applicant_id', 'id');
    }
    public function vacancy()
    {
        return $this->belongsTo(Vacancy::class, 'vacancy_id', 'id');
    }
}
