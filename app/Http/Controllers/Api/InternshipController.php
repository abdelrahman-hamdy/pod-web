<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\InternshipResource;
use App\Models\Internship;
use App\Models\InternshipApplication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InternshipController extends BaseApiController
{
    /**
     * Display a listing of internships.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Internship::where('status', 'open')
            ->with('category')
            ->orderBy('application_deadline', 'asc');

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                    ->orWhere('description', 'like', "%{$request->search}%")
                    ->orWhere('company_name', 'like', "%{$request->search}%");
            });
        }

        $internships = $query->paginate($request->get('per_page', 9));

        // Include categories in response for better performance
        $categories = \App\Models\InternshipCategory::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'color']);

        return $this->paginatedResponse($internships, [
            'categories' => $categories,
        ]);
    }

    /**
     * Display the specified internship.
     */
    public function show(Internship $internship): JsonResponse
    {
        $internship->load('category');

        return $this->successResponse(new InternshipResource($internship));
    }

    /**
     * Apply for an internship.
     */
    public function apply(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'internship_id' => 'required|exists:internships,id',
            'cover_letter' => 'required|string|max:2000',
            'resume' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'university' => 'nullable|string|max:255',
            'major' => 'nullable|string|max:255',
            'graduation_status' => 'nullable|string',
            'experience' => 'nullable|string|max:2000',
            'availability_start' => 'nullable|date',
            'availability_end' => 'nullable|date|after:availability_start',
        ]);

        $internship = Internship::findOrFail($validated['internship_id']);

        // Check if already applied
        if (InternshipApplication::where('internship_id', $internship->id)
            ->where('user_id', $request->user()->id)
            ->exists()) {
            return $this->errorResponse('You have already applied for this internship', null, 400);
        }

        // Check if application deadline has passed
        if ($internship->application_deadline && $internship->application_deadline < now()) {
            return $this->errorResponse('Application deadline has passed', null, 400);
        }

        // Check if internship is still open
        if ($internship->status !== 'open') {
            return $this->errorResponse('This internship is no longer accepting applications', null, 400);
        }

        $user = $request->user();
        $applicationData = [
            'internship_id' => $internship->id,
            'user_id' => $user->id,
            'full_name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone ?? '',
            'university' => $validated['university'] ?? '',
            'major' => $validated['major'] ?? '',
            'graduation_status' => $validated['graduation_status'] ?? 'student',
            'experience' => $validated['experience'] ?? '',
            'availability_start' => $validated['availability_start'] ?? ($internship->start_date ? $internship->start_date->format('Y-m-d') : now()->format('Y-m-d')),
            'availability_end' => $validated['availability_end'] ?? ($internship->start_date ? $internship->start_date->copy()->addMonths($internship->duration ?? 3)->format('Y-m-d') : now()->addMonths(3)->format('Y-m-d')),
            'motivation' => $validated['cover_letter'],
            'cover_letter' => $validated['cover_letter'],
            'status' => \App\InternshipApplicationStatus::PENDING,
        ];

        // Handle resume upload
        if ($request->hasFile('resume')) {
            $applicationData['resume'] = $request->file('resume')
                ->store('internships/resumes', 'public');
        }

        $application = InternshipApplication::create($applicationData);

        return $this->successResponse([
            'application_id' => $application->id,
            'status' => $application->status->value,
        ], 'Application submitted successfully', 201);
    }

    /**
     * Get user's internship applications.
     */
    public function myApplications(Request $request): JsonResponse
    {
        $applications = InternshipApplication::where('user_id', $request->user()->id)
            ->with(['internship.category'])
            ->latest()
            ->paginate($request->get('per_page', 15));

        return $this->paginatedResponse($applications);
    }

    /**
     * Get internship categories.
     */
    public function categories(Request $request): JsonResponse
    {
        $categories = \App\Models\InternshipCategory::where('is_active', true)
            ->orderBy('name')
            ->get();

        return $this->successResponse(
            \App\Http\Resources\CategoryResource::collection($categories)
        );
    }
}
