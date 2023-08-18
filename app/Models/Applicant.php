<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


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
    ];


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
        return $this->hasMany(Application::class);
    }

}
