<?php

namespace App\Http\Middleware;

use App\Http\Resources\CustomResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
class CheckForPaidOrders
{
    use CustomResponse;
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $inStoreOrder = $request->route('inStoreOrder');
        if($inStoreOrder->status == 'Paid'){
            return self::customResponse('This action cannot be done on an already paid product', null, 422);
        }
        return $next($request);
    }
}
