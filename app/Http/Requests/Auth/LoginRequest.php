<?php

namespace App\Http\Requests\Auth;

use App\Exceptions\AccountDeactivatedException;
use Exception;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        return true;
        // return $this->authorizeLocation()
        // ? Response::allow()
        // : Response::denyWithStatus(401, 'Unauthorized');
    }

    protected function authorizeLocation()
    {
        $apiKey = "5d9612032736493f841d5b00e2cbdad4";
        $deniedCountries = ['United States'];
        // $ip = "209.142.68.29"; //A United States IP.
        $ip = $this->ip();
        $fields = 'country_name';
        $location = $this->get_geolocation($apiKey, $ip, "en", $fields, "");
        $decodedLocation = json_decode($location, true);
        // dd($decodedLocation);
        if(in_array($decodedLocation['country_name'], $deniedCountries)){
            return false;
        }
        return true;
    }

    protected function get_geolocation($apiKey, $ip, $lang = "en", $fields = "*", $excludes = "") {
        $url = "https://api.ipgeolocation.io/ipgeo?apiKey={$apiKey}&ip={$ip}&lang={$lang}&fields={$fields}&excludes={$excludes}";
        $cURL = curl_init();

        curl_setopt($cURL, CURLOPT_URL, $url);
        curl_setopt($cURL, CURLOPT_HTTPGET, true);
        curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($cURL, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Accept: application/json'
        ));

        return curl_exec($cURL);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        // $this->authorize();
        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        // Check if the user's account status is "Active" and log them out if not so
        $user = Auth::user();
        if ($user->account_status != 'Active') {
            Auth::guard('web')->logout();
            throw new AccountDeactivatedException();
        }

        RateLimiter::clear($this->throttleKey());
    }


    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->input('email')).'|'.$this->ip());
    }
}
