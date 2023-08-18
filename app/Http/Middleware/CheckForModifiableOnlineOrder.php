<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Resources\CustomResponse;
class CheckForModifiableOnlineOrder
{
    use CustomResponse;
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $onlineOrder = $request->route('onlineOrder');
        if($onlineOrder->status != 'Review'){
            return self::customResponse('This action cannot be done on an already processed order', null, 422);
        }
        return $next($request);
    }
}
