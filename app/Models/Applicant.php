<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Vacancy;


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
        'resource',
        'status', 'vacancy_type'
    ];


    // public function changeStatus($applicant, $status)
    // {
    //     $vacancy = Vacancy::find($applicant->vacancy_type);

    //     if (!$vacancy) {
    //         throw new \Exception('Vacancy type not found.');
    //     }

    //     switch ($status) {
    //         case 'accepted':
    //             $this->handleAcceptedStatus($applicant, $vacancy);
    //             break;

    //         case 'rejected':
    //             $this->handleRejectedStatus($applicant);
    //             break;

    //         default:
    //             throw new \InvalidArgumentException('Invalid status.');
    //     }
    // }

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
        return $this->hasMany(Application::class);
    }

    public function changeStatus($status)
    {
        if ($status == 'accepted') {
            $this->status = 'مقبول';
        } elseif ($status == 'rejected') {
            $this->status = 'مرفوض';
        }
        $this->save();
    }
    
}
