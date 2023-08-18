<?php

namespace App\Http\Controllers;

use App\Http\Resources\Application\ApplicantResource;
use App\Http\Resources\Application\ApplicantCollection;
use App\Http\Resources\CustomResponse;
use Illuminate\Http\Request;
use App\Models\Applicant;
use Illuminate\Support\Facades\Validator;

class ApplicantController extends Controller
{
    use CustomResponse;
    public function index()
    {
        $this->authorize('viewAll', Applicant::class);
        return new ApplicantCollection(Applicant::paginate(10));
    }

    public function show(Applicant $applicant)
    {
        $this->authorize('view', $applicant);
        return self::customResponse(new ApplicantResource($applicant));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'mobile' => 'required|string',
            'vacancy' => 'required|string',
            'date_of_birth' => 'required|date|before:-18years',
            'gender' => 'required|string|in:Male,Female'
        ]);

        if ($validator->fails()) {
            return self::customResponse('errors', $validator->errors(), 422);
        }

        $applicant = Applicant::make($request->all());

        return self::customResponse('applicant created', new ApplicantResource($applicant), 200);
    }

    public function storeResume(Request $request, Applicant $applicant)
    {
        $validator = Validator::make($request->all(), [
            'resume' => 'required|file|mimes:pdf|max:10000',
        ]);

        if ($validator->fails()) {
            return self::customResponse('errors', $validator->errors(), 422);
        }

        $resume = $applicant->setResume($request->file('resume'));
        return self::customResponse('Resume set', $resume, 200);
    }

    public function getResume(Applicant $applicant)
    {
        $this->authorize('view', $applicant);
        $resume = $applicant->getResume();
        return self::customResponse('resume returned', $resume, 200);
    }
}
