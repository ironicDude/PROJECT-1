<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use App\Models\User;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/dashboard';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('api')
                ->prefix('api/auth')
                ->group(base_path('routes/api/auth.php'));

            Route::middleware('api')
                ->prefix('api/users')
                ->group(base_path('routes/api/users.php'));

            Route::middleware('api')
                ->prefix('api/products')
                ->group(base_path('routes/api/products.php'));

            Route::middleware('api')
                ->prefix('api/notifications')
                ->group(base_path('routes/api/notifications.php'));

            Route::middleware('api')
                ->prefix('api/drugs')
                ->group(base_path('routes/api/drugs.php'));

            Route::middleware('api')
                ->prefix('api/dashboard')
                ->group(base_path('routes/api/dashboard.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });

        Route::model('user_id', User::class, function ($value) {
            return User::where('id', $value)->firstOrFail();
        });
    }
}
