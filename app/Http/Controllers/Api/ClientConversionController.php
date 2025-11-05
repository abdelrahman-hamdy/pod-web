<?php

namespace App\Http\Controllers\Api;

use App\Models\ClientConversionRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientConversionController extends BaseApiController
{
    /**
     * Get the authenticated user's business request status.
     */
    public function getStatus(Request $request): JsonResponse
    {
        $user = $request->user();

        $conversionRequest = $user->clientConversionRequests()
            ->latest()
            ->first();

        if (! $conversionRequest) {
            return $this->errorResponse('No business account request found', null, 404);
        }

        return $this->successResponse($conversionRequest);
    }

    /**
     * Submit a new business account request.
     */
    public function submit(Request $request): JsonResponse
    {
        $user = $request->user();

        // Check if user already has a pending request
        $existingRequest = $user->clientConversionRequests()
            ->where('status', 'pending')
            ->first();

        if ($existingRequest) {
            return $this->validationErrorResponse([
                'request' => ['You already have a pending business account request.'],
            ]);
        }

        // Check if user is already a client or admin
        if ($user->isClient() || $user->isAdmin()) {
            return $this->validationErrorResponse([
                'request' => ['You already have business or admin privileges.'],
            ]);
        }

        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'business_field' => ['required', 'string', 'max:255'],
            'company_website' => ['nullable', 'url', 'max:255'],
            'linkedin_company_page' => ['nullable', 'url', 'max:255'],
            'additional_info' => ['nullable', 'string', 'max:2000'],
        ]);

        $conversionRequest = $user->clientConversionRequests()->create($validated);

        return $this->successResponse(
            $conversionRequest,
            'Business account request submitted successfully. Our team will review it soon.',
            201
        );
    }
}
