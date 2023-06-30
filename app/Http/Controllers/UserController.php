<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
class UserController extends Controller
{
    //call the deactivate method of User on the user with this specific id
    public function activateOrDeactivate(User $user):JsonResponse
    {
        $accountStatus = $user->status->status;
        if($accountStatus == 'Active'){
            $user->deactivate();
            return response()->json(['User has been deactivated'], 200);
        }

        elseif($accountStatus == 'Blocked'){
            $user->activate();
            return response()->json(['User has been activated'], 200);
        }
    } //end of deactivate

}
