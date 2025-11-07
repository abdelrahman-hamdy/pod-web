<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class BaseApiController extends Controller
{
    /**
     * Return a successful JSON response.
     */
    protected function successResponse($data = null, ?string $message = null, int $statusCode = 200): JsonResponse
    {
        $response = [
            'success' => true,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        if ($message !== null) {
            $response['message'] = $message;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return a successful paginated JSON response.
     */
    protected function paginatedResponse($paginator, $additionalData = null, ?string $message = null, ?string $resourceClass = null, $transformedData = null): JsonResponse
    {
        $items = $transformedData
            ?? ($resourceClass
                ? $resourceClass::collection($paginator->getCollection())
                : $paginator->items());

        $response = [
            'success' => true,
            'data' => $items,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'from' => $paginator->firstItem(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'to' => $paginator->lastItem(),
                'total' => $paginator->total(),
            ],
            'links' => [
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
            ],
        ];

        // Merge additional data if provided
        if (is_array($additionalData)) {
            $response = array_merge($response, $additionalData);
        }

        if ($message !== null) {
            $response['message'] = $message;
        }

        return response()->json($response);
    }

    /**
     * Return an error JSON response.
     */
    protected function errorResponse(string $message, $errors = null, int $statusCode = 400): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return a validation error JSON response.
     */
    protected function validationErrorResponse($errors, ?string $message = 'Validation failed'): JsonResponse
    {
        return $this->errorResponse($message, $errors, 422);
    }

    /**
     * Return a not found JSON response.
     */
    protected function notFoundResponse(?string $message = 'Resource not found'): JsonResponse
    {
        return $this->errorResponse($message, null, 404);
    }

    /**
     * Return an unauthorized JSON response.
     */
    protected function unauthorizedResponse(?string $message = 'Unauthorized'): JsonResponse
    {
        return $this->errorResponse($message, null, 401);
    }

    /**
     * Return a forbidden JSON response.
     */
    protected function forbiddenResponse(?string $message = 'Forbidden'): JsonResponse
    {
        return $this->errorResponse($message, null, 403);
    }
}
