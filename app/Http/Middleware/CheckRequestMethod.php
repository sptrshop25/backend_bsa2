<?php

namespace App\Http\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class CheckRequestMethod
{
    public function handle($request, Closure $next, ...$methods)
    {
        if (!in_array($request->method(), $methods)) {
            throw new MethodNotAllowedHttpException($methods);
        }

        return $next($request);
    }
}
