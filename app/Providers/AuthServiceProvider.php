<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Response;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
        'App\Models\User' => 'App\Policies\UserPolicy',
        'App\Models\Product' => 'App\Policies\ProductPolicy',
        'App\Models\CartedProduct' => 'App\Policies\CartedProductPolicy',
        'App\Models\Cart' => 'App\Policies\CartPolicy',
        'App\Models\Customer' => 'App\Policies\CustomerPolicy',
        'App\Models\PurchasedProduct' => 'App\Policies\PurchasedProductPolicy',
        'App\Models\Order' => 'App\Policies\OrderPolicy',
        'App\Models\Payment' => 'App\Policies\PaymentPolicy',
        'App\Models\Vacancy' => 'App\Policies\VacancyPolicy',
        'App\Models\Application' => 'App\policies\ApplicationPolicy',
        'App\Models\Applicant' => 'App\policies\ApplicantPolicy',
        'App\Models\OnlineOrder' => 'App\policies\OnlineOrderPolicy'
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot()
    {
        $this->registerPolicies();

        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return config('app.frontend_url')."/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        });

        Gate::define('backup', function (User $user) {
            return $user->isEmployee() && $user->isAdministrator();
        });

        // Gate::define('activateOrDeactivate', function (User $user, User $toBeToggledUser) {
        //     return $user->isAdministrator() && !$toBeToggledUser->isAdministrator()
        //         ? true
        //         : false;
        // });

        //
    }
}
