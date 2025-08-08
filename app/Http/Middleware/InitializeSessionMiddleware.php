<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class InitializeSessionMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Asegurar que las sesiones estén configuradas
        if (!Session::isStarted()) {
            Session::start();
        }

        return $next($request);
    }
}
