<?php

namespace App\Http\Controllers\Api;

use App\ExperienceLevel;
use App\Http\Resources\JobListingResource;
use App\LocationType;
use App\Models\JobApplication;
use App\Models\JobListing;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobListingController extends BaseApiController
{
    use AuthorizesRequests;

    /**
     * Display a listing of job listings.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Eager load relationships including user's application if authenticated
        $with = ['category', 'poster'];
        if ($user) {
            $with[] = 'userApplication';
        }
        
        $query = JobListing::with($with)
            ->active()
            ->acceptingApplications()
            ->latest();

        // Apply filters
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('experience_level')) {
            $query->forExperienceLevel($request->experience_level);
        }

        if ($request->filled('location_type')) {
            $query->byLocationType($request->location_type);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('skills')) {
            $skills = explode(',', $request->skills);
            $query->withSkills($skills);
        }

        if ($request->filled('salary_range')) {
            match ($request->salary_range) {
                'under_50k' => $query->where('salary_max', '<', 50000),
                '50k_100k' => $query->where(function ($q) {
                    $q->whereBetween('salary_min', [50000, 100000])
                        ->orWhereBetween('salary_max', [50000, 100000]);
                }),
                '100k_150k' => $query->where(function ($q) {
                    $q->whereBetween('salary_min', [100000, 150000])
                        ->orWhereBetween('salary_max', [100000, 150000]);
                }),
                'over_150k' => $query->where('salary_min', '>', 150000),
                default => null,
            };
        }

        if ($request->filled('date_posted')) {
            match ($request->date_posted) {
                'today' => $query->whereDate('created_at', today()),
                'this_week' => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]),
                'this_month' => $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year),
                'last_month' => $query->whereMonth('created_at', now()->subMonth()->month)->whereYear('created_at', now()->subMonth()->year),
                default => null,
            };
        }

        if ($request->filled('company')) {
            $query->where('company_name', 'like', "%{$request->company}%");
        }

        $jobs = $query->paginate($request->get('per_page', 12));

        return $this->paginatedResponse($jobs);
    }

    /**
     * Store a newly created job listing.
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', JobListing::class);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'company_name' => 'required|string|max:255',
            'company_description' => 'nullable|string',
            'location_type' => 'required|in:'.implode(',', array_column(LocationType::cases(), 'value')),
            'location' => 'nullable|required_unless:location_type,remote|string|max:255',
            'salary_min' => 'nullable|numeric|min:0',
            'salary_max' => 'nullable|numeric|min:0|gte:salary_min',
            'required_skills' => 'required|array|min:1',
            'required_skills.*' => 'string|max:50',
            'experience_level' => 'required|in:'.implode(',', array_column(ExperienceLevel::cases(), 'value')),
            'application_deadline' => 'required|date|after:today',
            'category_id' => 'required|exists:categories,id',
        ]);

        $validated['posted_by'] = $request->user()->id;
        $validated['status'] = 'active';

        $job = JobListing::create($validated);
        $job->load(['category', 'poster']);

        return $this->successResponse(new JobListingResource($job), 'Job listing created successfully', 201);
    }

    /**
     * Display the specified job listing.
     */
    public function show(Request $request, JobListing $job): JsonResponse
    {
        $user = $request->user();
        
        // Load relationships including user's application if authenticated
        $with = ['category', 'poster'];
        if ($user) {
            $with[] = 'userApplication';
        }
        
        $job->load($with);

        return $this->successResponse(new JobListingResource($job));
    }

    /**
     * Update the specified job listing.
     */
    public function update(Request $request, JobListing $job): JsonResponse
    {
        $this->authorize('update', $job);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'company_name' => 'sometimes|required|string|max:255',
            'company_description' => 'nullable|string',
            'location_type' => 'sometimes|required|in:'.implode(',', array_column(LocationType::cases(), 'value')),
            'location' => 'sometimes|required_unless:location_type,remote|string|max:255',
            'salary_min' => 'nullable|numeric|min:0',
            'salary_max' => 'nullable|numeric|min:0|gte:salary_min',
            'required_skills' => 'sometimes|required|array|min:1',
            'required_skills.*' => 'string|max:50',
            'experience_level' => 'sometimes|required|in:'.implode(',', array_column(ExperienceLevel::cases(), 'value')),
            'application_deadline' => 'sometimes|required|date',
            'category_id' => 'sometimes|required|exists:categories,id',
            'status' => 'sometimes|required|in:active,closed,archived',
        ]);

        $job->update($validated);
        $job->load(['category', 'poster']);

        return $this->successResponse(new JobListingResource($job), 'Job listing updated successfully');
    }

    /**
     * Remove the specified job listing.
     */
    public function destroy(Request $request, JobListing $job): JsonResponse
    {
        $this->authorize('delete', $job);

        $job->delete();

        return $this->successResponse(null, 'Job listing deleted successfully', 204);
    }

    /**
     * Apply for a job.
     */
    public function apply(Request $request, JobListing $job): JsonResponse
    {
        if (! $job->canUserApply($request->user())) {
            $reason = 'Unknown reason';
            if (! $job->isAcceptingApplications()) {
                $reason = $job->status !== \App\JobStatus::ACTIVE->value
                    ? 'Job is not active'
                    : ($job->hasDeadlinePassed() ? 'Application deadline has passed' : 'Job is not accepting applications');
            } elseif ($job->applications()->where('user_id', $request->user()->id)->exists()) {
                $reason = 'You have already applied for this job';
            }

            return $this->errorResponse('You cannot apply for this job. '.$reason, null, 400);
        }

        $validated = $request->validate([
            'cover_letter' => 'required|string|max:2000',
            'additional_info' => 'nullable|string|max:1000',
        ]);

        try {
            $application = $job->applyForJob($request->user(), $validated);

            return $this->successResponse([
                'application_id' => $application->id,
                'status' => $application->status->value,
            ], 'Application submitted successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to submit application: '.$e->getMessage(), null, 500);
        }
    }

    /**
     * Get user's job applications.
     */
    public function myApplications(Request $request): JsonResponse
    {
        $applications = JobApplication::where('user_id', $request->user()->id)
            ->with(['jobListing.category'])
            ->latest()
            ->paginate($request->get('per_page', 15));

        return $this->paginatedResponse($applications);
    }

    /**
     * Close a job listing.
     */
    public function close(Request $request, JobListing $job): JsonResponse
    {
        $this->authorize('update', $job);

        $job->update(['status' => 'closed']);

        return $this->successResponse(new JobListingResource($job), 'Job listing closed successfully');
    }

    /**
     * Reopen a job listing.
     */
    public function reopen(Request $request, JobListing $job): JsonResponse
    {
        $this->authorize('update', $job);

        $job->update(['status' => 'active']);

        return $this->successResponse(new JobListingResource($job), 'Job listing reopened successfully');
    }

    /**
     * Archive a job listing.
     */
    public function archive(Request $request, JobListing $job): JsonResponse
    {
        $this->authorize('update', $job);

        $job->update(['status' => 'archived']);

        return $this->successResponse(new JobListingResource($job), 'Job listing archived successfully');
    }

    /**
     * Get job applications (for job creator/admin).
     */
    public function applications(Request $request, JobListing $job): JsonResponse
    {
        $this->authorize('view-applications', $job);

        $applications = $job->applications()
            ->with('user')
            ->latest()
            ->paginate($request->get('per_page', 25));

        return $this->paginatedResponse($applications);
    }

    /**
     * Review a job application.
     */
    public function reviewApplication(Request $request, JobApplication $application): JsonResponse
    {
        $this->authorize('view-applications', $application->jobListing);

        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $application->update([
            'status' => \App\JobApplicationStatus::REVIEWED,
            'admin_notes' => $validated['notes'] ?? null,
            'reviewed_at' => now(),
            'admin_id' => $request->user()->id,
        ]);

        return $this->successResponse(null, 'Application marked as reviewed');
    }

    /**
     * Accept a job application.
     */
    public function acceptApplication(Request $request, JobApplication $application): JsonResponse
    {
        $this->authorize('view-applications', $application->jobListing);

        $application->accept();

        return $this->successResponse(null, 'Application accepted successfully');
    }

    /**
     * Reject a job application.
     */
    public function rejectApplication(Request $request, JobApplication $application): JsonResponse
    {
        $this->authorize('view-applications', $application->jobListing);

        $validated = $request->validate([
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        $application->reject($validated['rejection_reason'] ?? null);

        return $this->successResponse(null, 'Application rejected');
    }

    /**
     * Update application notes.
     */
    public function updateApplicationNotes(Request $request, JobApplication $application): JsonResponse
    {
        $this->authorize('view-applications', $application->jobListing);

        $validated = $request->validate([
            'notes' => 'required|string|max:1000',
        ]);

        $application->update(['admin_notes' => $validated['notes']]);

        return $this->successResponse(null, 'Application notes updated');
    }
}
