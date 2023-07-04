<?php

namespace App\Http\Controllers;

use App\Events\UserAccountStatusChanged;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\CustomResponse;
use App\Models\Employee;
use App\Notifications\UserAccountStatusChangedNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Policies\UserPolicy;
class UserController extends Controller
{
    use CustomResponse;

    //call the the User activate and deactivate methods on the user with this specific id
    public function activateOrDeactivate(Request $request, User $user): JsonResponse
    {
        $this->authorize('activateOrDeactivate', $user);

        $cacheKey = 'activateOrDeactivate2_' . $request->user()->id;
        $interval = 60 * 60;
        $lastMethodCall = Cache::get($cacheKey);

        if ($lastMethodCall && (time() - $lastMethodCall < $interval)) {
            $timeLeft = intval((($lastMethodCall + $interval) - time()) / 60);
            return $this->customResponse("Please, wait for $timeLeft minutes before calling this method again", null, 429);
        } else {
            Cache::put($cacheKey, time(), $interval);
            $accountStatus = $user->accountStatus->status;
            if ($accountStatus == 'Active') {
                $user->deactivate();
            } elseif ($accountStatus == 'Blocked') {
                $user->activate();
            }
            $accountStatus = $user->fresh()->accountStatus->status;
            event(new UserAccountStatusChanged($request->user(), $user, $accountStatus));
            return $this->customResponse("User is now {$accountStatus}", new UserResource($user), 200);
        }
    } //end of activateOrDeactivate
}
