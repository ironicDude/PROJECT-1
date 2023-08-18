<?php

namespace App\Http\Controllers;

use App\Http\Resources\Application\ApplicationCollection;
use App\Http\Resources\Application\ApplicationResource;
use App\Http\Resources\CustomResponse;
use Illuminate\Http\Request;
use App\Models\Vacancy;
use App\Models\Applicant;
use App\Mail\ApplicantMail;
use App\Mail\RejectMail;
use App\Mail\AcceptMail;
use App\Models\Application;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ApplicationController extends Controller
{
    use CustomResponse;

    public function accept(Application $application)
    {
        $application->accept();
        return self::customResponse('Application accepted', $application, 200);
    }

    public function index()
    {
        return new ApplicationCollection(Application::paginate(15));
    }

    public function show(Application $application)
    {
        return self::customResponse('Application returned', new ApplicationResource($application), 200);
    }

}
