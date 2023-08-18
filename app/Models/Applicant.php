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
        $first_name = $info['first_name'];
        $last_name = $info['last_name'];
        $email = $info['email'];
        $mobile = $info['mobile'];
        $address = $info['address'];
        $dateOfBirth = $info['date_of_birth'];
        $gender = $info['gender'];
        $vacancyId = Vacancy::firstWhere('title', $info['vacancy'])->value('id');

        $applicant = self::create([
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'mobile' => $mobile,
            'address' => $address,
            'date_of_birth' => $dateOfBirth,
            'gender' => $gender,
        ]);

        Application::create([
            'applicant_id' => $applicant->id,
            'vacancy_id' => $vacancyId,
            'status' => 'Review',
        ]);

        Mail::to($applicant)->send(new ApplicantMail);

        return $applicant;

    }

    public function setResume(UploadedFile $resume)
    {
        if ($this->resume) {
            Storage::disk('local')->delete("resume/{$this->resume}");
        }
        $resumeName = "Applicant{$this->id}.{$resume->getClientOriginalExtension()}";
        Storage::disk('local')->put("resumes/{$resumeName}", File::get($resume));
        $this->resume = $resume;
        $this->save();
        return $this->getResume();
    }

    public function getResume()
    {
        $resumeName = $this->resume;
        if(!$resumeName){
            return null;
        }
        $resumeContent = file_get_contents("C:\\Programming\Laravel\PROJECT-1\storage\app\resumes\\{$resumeName}");
        $encodedContent = base64_encode($resumeContent);
        $resumeData = mb_convert_encoding("data:application/pdf;base64,{$encodedContent}", 'UTF-8');
        return $resumeData;
    }


    public function generateWorkEmail()
    {
        $email = "{$this->first_name}{$this->last_name}{$this->id}@remedyat.com";
        return $email;
    }

    //////////////////////////////////////////////////

    public function changeStatus($applicant, $status)
    {
        $vacancy = Vacancy::find($applicant->vacancy_type);

        if (!$vacancy) {
            throw new \Exception('Vacancy type not found.');
        }

        switch ($status) {
            case 'accepted':
                $this->handleAcceptedStatus($applicant, $vacancy);
                break;

            case 'rejected':
                $this->handleRejectedStatus($applicant);
                break;

            default:
                throw new \InvalidArgumentException('Invalid status.');
        }
    }

    private function handleAcceptedStatus($applicant, $vacancy)
    {
        if ($vacancy->number_of_vacancies > 0) {
            $applicant->status = 'Accepted';
            $applicant->save();

            $vacancy->number_of_vacancies--;
            $vacancy->save();
        } else {
            $vacancy->status = 'Not Available';
            $vacancy->save();

            throw new \Exception('No vacancies available.');
        }
    }

    private function handleRejectedStatus($applicant)
    {
        $applicant->status = 'Rejected';
        $applicant->save();
    }

    public function applications()
    {
        return $this->hasMany(Application::class, 'applicant_id', 'id');
    }

}
