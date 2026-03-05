<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * URIs that should be excluded from CSRF verification.
     * Ovo je korisno za API rute ako koristiš token.
     */
    protected $except = [
        // '/api/*', '/login', '/register', '/password/*', ...
    ];
}