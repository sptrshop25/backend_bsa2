<?php

namespace App\Http\Middleware;

use App\Http\Controllers\AksesAPiController;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecretKeyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apikey = new AksesAPiController();
        $key = $request->header('Authorization');
        if ($apikey->apikey($key) == false) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        return $next($request);
    }
}
