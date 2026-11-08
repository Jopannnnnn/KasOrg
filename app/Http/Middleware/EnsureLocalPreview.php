<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureLocalPreview
{
    public function handle(Request $request, Closure $next): Response
    {
        // Pertahanan runtime jika cache route local terbawa ke deployment lain.
        abort_unless(app()->environment('local'), 404);

        return $next($request);
    }
}
