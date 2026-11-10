<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public static function success(mixed $data, string $message): JsonResponse
    {
        return response()->json(['success' => true, 'message' => $message, 'data' => $data, 'errors' => null])
            ->header('Cache-Control', 'private, no-store');
    }

    public static function error(string $message, int $status, mixed $errors = null): JsonResponse
    {
        return response()->json(['success' => false, 'message' => $message, 'data' => null, 'errors' => $errors], $status)
            ->header('Cache-Control', 'private, no-store');
    }
}
