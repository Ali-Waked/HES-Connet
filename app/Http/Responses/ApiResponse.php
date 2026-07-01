<?php

declare(strict_types=1);

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    protected function respond(mixed $data = null, string $message = '', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => $code >= 200 && $code < 300,
            'message' => __($message),
            'data' => $data,
        ], $code);
    }

    protected function respondCreated(mixed $data, string $message = 'Created successfully.'): JsonResponse
    {
        return $this->respond($data, $message, 201);
    }

    protected function respondUpdated(mixed $data, string $message = 'Updated successfully.'): JsonResponse
    {
        return $this->respond($data, $message, 200);
    }

    protected function respondDeleted(string $message = 'Deleted successfully.'): JsonResponse
    {
        return $this->respond(null, $message, 200);
    }

    protected function respondError(string $message, int $code = 400, mixed $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => __($message),
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    protected function respondNotFound(string $message = 'Resource not found.'): JsonResponse
    {
        return $this->respondError($message, 404);
    }

    protected function respondForbidden(string $message = 'Forbidden.'): JsonResponse
    {
        return $this->respondError($message, 403);
    }

    protected function respondUnauthorized(string $message = 'Unauthorized.'): JsonResponse
    {
        return $this->respondError($message, 401);
    }
}
