<?php

namespace App\Http\Controllers;

use App\Exceptions\ApplicationAlreadyAcceptedException;
use App\Exceptions\ApplicationAlreadyRejectedException;
use App\Http\Resources\Application\ApplicationCollection;
use App\Http\Resources\Application\ApplicationOverviewCollection;
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

    public function index()
    {
        $this->authorize('viewAll', Application::class);
        return new ApplicationOverviewCollection(Application::paginate(15));
    }

    public function show(Application $application)
    {
        $this->authorize('view', $application);
        return self::customResponse('Application returned', new ApplicationResource($application), 200);
    }

    public function accept(Application $application)
    {
        $this->authorize('accept', $application);
        try {
            $application->accept();
        } catch (ApplicationAlreadyAcceptedException $e) {
            return self::customResponse($e->getMessage(), null, 422);
        } catch (ApplicationAlreadyRejectedException $e) {
            return self::customResponse($e->getMessage(), null, 422);
        }
        return self::customResponse('Application accepted', new ApplicationResource($application), 200);
    }

    public function reject(Application $application, Request $request)
    {
        $this->authorize('reject', $application);
        $validator = Validator::make($request->all(),
        [
            'reason' => 'required|string',
        ]);
        if($validator->fails()){
            return self::customResponse('errors', $validator->errors(), 422);
        }
        try {
            $application->reject($request->reason);
        } catch (ApplicationAlreadyAcceptedException $e) {
            return self::customResponse($e->getMessage(), null, 422);
        } catch (ApplicationAlreadyRejectedException $e) {
            return self::customResponse($e->getMessage(), null, 422);
        }
        return self::customResponse('Application rejected', new ApplicationResource($application), 200);
    }

}
