<?php

namespace App\Http\Controllers\User;

use App\Events\UserAccountStatusChanged;
use App\Exceptions\AccountAlreadyRestoredException;
use App\Exceptions\AccountPermanentlyDeletedException;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\CustomResponse;
use App\Http\Resources\User\UserResource;
use App\Models\Employee;
use App\Notifications\UserAccountStatusChangedNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Policies\UserPolicy;
use Carbon\Carbon;

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
            $accountStatus = $user->account_status;

            // Toggle the user's account status between 'Active' and 'Blocked'.
            if ($accountStatus == 'Active') {
                $user->deactivate();
            } elseif ($accountStatus == 'Blocked') {
                $user->activate();
            }

            // Refresh the user model to get the updated account status.
            $accountStatus = strtolower($user->fresh()->account_status);

            // Broadcast an event to notify about the change in user account status.
            event(new UserAccountStatusChanged($request->user(), $user, $accountStatus));
            // Return a custom response indicating the new account status and the user information in UserResource format.
            return self::customResponse("User is now {$accountStatus}", new UserResource($user), 200);
        }
    }



    public function getAddress(User $user)
    {
        $this->authorize('getInfo', $user);
        $address = $user->getAddress();
        return self::customResponse('Address returned', $address, 200);
    }

    public function setAddress(Request $request)
    {
        // Validate the incoming request data using the specified validation rules.
        $validator = Validator::make($request->all(), [
            'address' => 'required|string|max:255',
        ]);

        // If the validation fails, return a JSON response with the validation errors and status code 422 (Unprocessable Entity).
        if ($validator->fails()) {
            return self::customResponse('errors', $validator->errors(), 422);
        }
        $address = Auth::user()->setAddress($request->address);
        return self::customResponse('Address returned', $address, 200);
    }

    public function getImage(User $user)
    {
        $image = $user->getImage();
        return self::customResponse('Image returned', $image, 200);
    }

    public function setImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048', // Adjust the allowed mime types and max file size as needed (2 MB in this example)
        ]);

        if ($validator->fails()) {
            return self::customResponse('errors', $validator->errors(), 422);
        }

        $image = Auth::user()->setImage($request->file('image'));
        return self::customResponse('Image set', $image, 200);
    }


    public function getFirstName(User $user)
    {
        $firstName =  $user->getFirstName();
        return self::customResponse('First name returned', $firstName, 200);
    }

    public function setFirstName(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstName' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return self::customResponse('Validation Error', $validator->errors(), 422);
        }

        $firstName = Auth::user()->setFirstName($request->input('firstName'));
        return self::customResponse('First name set', $firstName, 200);
    }


    public function getLastName(User $user)
    {
        $lastName =  $user->getLastName();
        return self::customResponse('Last name set', $lastName, 200);
    }

    public function setLastName(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lastName' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return self::customResponse('Validation Error', $validator->errors(), 422);
        }

        $lastName = Auth::user()->setLastName($request->input('lastName'));
        return self::customResponse('Last name returned', $lastName, 200);
    }


    public function getMobile(User $user)
    {
        $this->authorize('getInfo', $user);
        $mobile =  $user->getMobile();
        return self::customResponse('Mobile returned', $mobile, 200);
    }

    public function setMobile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return self::customResponse('Validation Error', $validator->errors(), 422);
        }

        $mobile = Auth::user()->setMobile($request->input('mobile'));
        return self::customResponse('Mobile set', $mobile, 200);
    }


    public function getDateOfBirth(User $user)
    {
        $this->authorize('getInfo', $user);
        $dateOfBirth =  $user->getDateOfBirth();
        return self::customResponse('Date of birth set', $dateOfBirth, 200);
    }

    public function setDateOfBirth(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date|before:-10 years|date_format:Y-m-d',
        ]);

        if ($validator->fails()) {
            return self::customResponse('Validation Error', $validator->errors(), 422);
        }

        $dateOfBirth = Auth::user()->setDateOfBirth($request->date);
        return self::customResponse('Date of birth returned', $dateOfBirth, 200);
    }


    public function getGender(User $user)
    {
        $gender =  $user->getGender();
        return self::customResponse('Gender set', $gender, 200);
    }

    public function setGender(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gender' => 'required|string|in:Male,Female,I prefer not to say',
        ]);

        if ($validator->fails()) {
            return self::customResponse('Validation Error', $validator->errors(), 422);
        }

        $gender = Auth::user()->setGender($request->input('gender'));
        return self::customResponse('Gender returned', $gender, 200);
    }


    public function getAccountStatus(User $user)
    {
        $this->authorize('getInfo', $user);
        $accountStatus =  $user->getAccountStatus();
        return self::customResponse('Account status set', $accountStatus, 200);
    }

    public function getType(User $user)
    {
        $type =  $user->getType();
        return self::customResponse('Type set', $type, 200);
    }

    public function updateInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:' . User::class,
            'dateOfBirth' => 'required|date|before:-10years',
            'gender' => 'required|string|in:Male,Female,I prefer not to say',
            'mobile' => 'required|integer',
        ]);

        // If the validation fails, return a JSON response with the validation errors and status code 422 (Unprocessable Entity).
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $newInfo = $request->toArray();
        $userInfo = new UserResource(Auth::user()->updateInfo($newInfo));
        return self::customResponse('User with new info', $userInfo, 200);
    }

    public function deleteSoftly()
    {
        $user = Auth::user()->deleteSoftly();
        return self::customResponse('We are sorry to hear that you want to leave us. On the bright side, you can still restore your account if you log in within 14 days from now.', $user, 200);
    }

    public function restore(Request $request, int $userId)
    {
        $user = User::withTrashed()->findOrFail($userId);
        $this->authorize('restore', $user);
        if (!$request->hasValidSignature()) {
            abort(401);
        }
        try{
            $result = $user->restoreAccount();
        } catch(AccountPermanentlyDeletedException $e){
            return self::customResponse($e->getMessage(), null, 401);
        } catch(AccountAlreadyRestoredException $e){
            return self::customResponse($e->getMessage(), null, 401);
        }
        return self::customResponse('Your account has been restored', $result, 202);
    }

}
