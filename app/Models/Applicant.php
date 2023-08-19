<?php

namespace App\Models;

use App\Mail\ApplicantMail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class Applicant extends Model
{

    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'mobile',
        'status',
        'address',
        'date_of_birth',
        'gender'
    ];

    public static function make(array $info)
    {
        $vacancyId = Vacancy::firstWhere('title', $info['vacancy'])->value('id');

        $applicant = self::create([
            'first_name' => $info['first_name'],
            'last_name' => $info['last_name'],
            'email' => $info['email'],
            'mobile' => $info['mobile'],
            'address' => $info['address'],
            'date_of_birth' => $info['date_of_birth'],
            'gender' => $info['gender'],
        ]);

        $applicant->setResume($info['resume']);

        $applicant->applications()->create([
            'applicant_id' => $applicant->id,
            'vacancy_id' => $vacancyId,
            'status' => 'Review',
        ]);

        Mail::to($applicant)->send(new ApplicantMail);

        return $applicant;

    }

    protected function setResume(UploadedFile $resume)
    {
        if ($this->resume) {
            Storage::disk('local')->delete("resumes/{$this->resume}");
        }
        $resumeName = "Applicant{$this->id}.{$resume->getClientOriginalExtension()}";
        Storage::disk('local')->put("resumes/{$resumeName}", File::get($resume));
        $this->resume = $resumeName;
        $this->save();
        return $this->getResume();
    }

    public function getResume()
    {
        $resumeName = $this->resume;
        if(!$resumeName){
            return null;
        }
        $resumeContent = file_get_contents("F:\\files\Pharmacy managment system _back-end\PROJECT-1-1\storage\app\\resumes\\{$resumeName}");
        $encodedContent = base64_encode($resumeContent);
        $resumeData = mb_convert_encoding("data:application/pdf;base64,{$encodedContent}", 'UTF-8');
        return $resumeData;
    }


    public function generateWorkEmail()
    {
        $email = "{$this->first_name}{$this->last_name}{$this->id}@remedya.com";
        return $email;
    }

    public function applications()
    {
        return $this->hasMany(Application::class, 'applicant_id', 'id');
    }

}
