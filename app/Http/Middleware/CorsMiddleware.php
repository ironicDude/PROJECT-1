<?php
namespace App\Http\Middleware;


use Closure;

class CorsMiddleware
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        
        $response->header('Access-Control-Allow-Origin', 'http://localhost:8000');
        
        return $response;
    }
}
