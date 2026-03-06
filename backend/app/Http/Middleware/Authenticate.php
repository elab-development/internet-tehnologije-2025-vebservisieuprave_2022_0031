<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Authenticate
{
    public function handle(Request $request, Closure $next, ...$guards)
    {
        if (!$request->user()) {
            return response()->json(['message' => 'Niste ulogovani.'], 401);
        }

        return $next($request);
    }
}