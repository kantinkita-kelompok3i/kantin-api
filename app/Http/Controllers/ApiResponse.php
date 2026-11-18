<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    protected function success(string $message, mixed $data = null, int $code = 200, ?array $meta = null): JsonResponse
    {
        $payload = ['status' => 'success', 'message' => $message, 'data' => $data];

        if ($meta !== null) {
            $payload['meta'] = $meta;
        }

        return response()->json($payload, $code);
    }

    protected function error(string $message, array $errors = [], int $code = 400): JsonResponse
    {
        return response()->json(['status' => 'error', 'message' => $message, 'errors' => (object) $errors], $code);
    }
}
