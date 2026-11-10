<?php

namespace App\Http\Middleware;

use App\Http\Responses\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiJson
{
    public function handle(Request $request, Closure $next): Response
    {
        $request->headers->set('Accept', 'application/json');
        $response = $next($request);
        // Middleware active yang sudah ada mengakhiri session akun nonaktif lalu redirect.
        // Untuk API, pertahankan tindakan keamanan tersebut dan kembalikan JSON 403.
        if ($response->isRedirection()) {
            return ApiResponse::error('Sesi berakhir atau akun tidak aktif. Silakan masuk kembali.', 403);
        }

        return $response;
    }
}
