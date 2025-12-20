<?php

namespace App\Helpers;

use Symfony\Component\HttpFoundation\Response;

class ApiResponse
{
    public static function success(
        string $message,
        mixed $data = [],
        int $code = Response::HTTP_OK,
        array $meta = [],
        array $links = []
    ): \Illuminate\Http\JsonResponse {
        $response = array_filter([
            'message' => $message,
            'error' => false,
            'code' => $code,
            'data' => $data,
            'meta' => $meta,
            'links' => $links,
        ], fn ($value) => ! (is_null($value) || $value === [] || $value === ''));

        return response()->json($response, $code);
    }

    public static function error(
        string $message,
        mixed $errors = [],
        int $code = Response::HTTP_INTERNAL_SERVER_ERROR
    ): \Illuminate\Http\JsonResponse {
        return response()->json([
            'message' => $message,
            'error' => true,
            'code' => $code,
            'errors' => $errors,
        ], $code);
    }
}
