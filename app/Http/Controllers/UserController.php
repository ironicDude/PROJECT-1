<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\CustomResponse;
use Illuminate\Support\Facades\Cache;
class UserController extends Controller
{
    use CustomResponse;

    //call the the User activate and deactivate methods on the user with this specific id
    public function activateOrDeactivate(User $user):JsonResponse
    {
        $cacheKey = 'activateOrDeactivate';
        $interval = 60*60;
        $lastMethodCall = Cache::get($cacheKey);

        if($lastMethodCall && (time() - $lastMethodCall < $interval))
        {
            $timeLeft = intval((($lastMethodCall + $interval) - time())/60);
            return $this->customResponse("Please, wait for $timeLeft minutes before calling this method again", null, 429);
        }
        else
        {
            Cache::put($cacheKey, time(), $interval);
            $accountStatus = $user->status->status;
            if($accountStatus == 'Active')
            {
                $user->deactivate();
                return $this->customResponse('User has been deactivated', new UserResource($user), 200);
            }

            elseif($accountStatus == 'Blocked')
            {
                $user->activate();
                return $this->customResponse('User has been activated', new UserResource($user), 200);
            }
        }
    } //end of activateOrDeactivate
}