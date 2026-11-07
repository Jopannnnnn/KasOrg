<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        abort_unless(in_array($request->user()?->role?->kode, $roles, true), 403,
            'Anda tidak memiliki izin untuk mengakses halaman ini.');

        return $next($request);
    }
}
