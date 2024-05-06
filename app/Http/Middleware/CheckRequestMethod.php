<?php

namespace App\Http\Middleware;

use Closure;

class CheckRequestMethod
{
    public function handle($request, Closure $next)
    {
        if ($request->method() !== 'POST') {
            abort(404);
        }

        return $next($request);
    }
}
