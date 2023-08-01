<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckForceLogout
{
    public function handle($request, Closure $next)
    {
        if (Auth::check() && Auth::user()->account_status == 'Blocked') {
            Auth::logout();
            return redirect('/login');
        }

        return $next($request);
    }
}
