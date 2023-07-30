<?php

namespace App\Http\Controllers\User;

use App\Events\UserAccountStatusChanged;
use App\Http\Controllers\Controller;
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
    /**
     * Activate or Deactivate a user account.
     *
     * This method activates or deactivates a user account based on the provided user model.
     * The method first checks if the authenticated user has the appropriate permission to perform
     * this action using the 'activateOrDeactivate' policy defined in the UserPolicy class.
     * To prevent abuse and rate-limiting, the method uses caching to limit the frequency of calls.
     * If the same authenticated user attempts to activate or deactivate accounts within a short time span,
     * the method will reject subsequent requests until a predefined interval passes (e.g., 60 minutes).
     * After verifying the validity of the request, the user's account status is toggled between 'Active' and 'Blocked'.
     * The updated account status is then broadcasted using the UserAccountStatusChanged event.
     * Finally, a custom response is returned, indicating the new account status and returning the user information
     * in a UserResource format if the operation was successful.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request containing the authenticated user.
     * @param \App\Models\User $user The User model instance to activate or deactivate.
     * @return \Illuminate\Http\JsonResponse The JSON response indicating the new account status and the user information.
     */
    public function activateOrDeactivate(Request $request, User $user): JsonResponse
    {
        // Check if the authenticated user has the permission to activate or deactivate user accounts.
        $this->authorize('activateOrDeactivate', $user);

        // Set up caching to prevent rate-limiting for the same user.
        $cacheKey = 'activateOrDeactivate_' . $request->user()->id;
        $interval = 60 * 60; // 60 minutes interval
        $lastMethodCall = Cache::get($cacheKey);

        // If the user has recently called this method, reject subsequent requests until the interval expires.
        if ($lastMethodCall && (time() - $lastMethodCall < $interval)) {
            $timeLeft = intval((($lastMethodCall + $interval) - time()) / 60); // Calculate remaining minutes.
            return self::customResponse("Please, wait for $timeLeft minutes before calling this method again", null, 429); // HTTP 429: Too Many Requests.
        } else {
            // Update the cache with the current timestamp to record the latest method call.
            Cache::put($cacheKey, time(), $interval);

            // Get the current account status of the user.
            $accountStatus = $user->accountStatus->status;

            // Toggle the user's account status between 'Active' and 'Blocked'.
            if ($accountStatus == 'Active') {
                $user->deactivate();
            } elseif ($accountStatus == 'Blocked') {
                $user->activate();
            }

            // Refresh the user model to get the updated account status.
            $accountStatus = $user->fresh()->accountStatus->status;

            // Broadcast an event to notify about the change in user account status.
            event(new UserAccountStatusChanged($request->user(), $user, $accountStatus));

            // Return a custom response indicating the new account status and the user information in UserResource format.
            return self::customResponse("User is now {$accountStatus}", new UserResource($user), 200);
        }
    }

    public function getAddress()
    {
        return self::customResponse('address returned', Auth::user()->address, 200);
    }

    
}
