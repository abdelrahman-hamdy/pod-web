<?php

namespace App\Http\Controllers;

use App\ExperienceLevel;
use App\LocationType;
use App\Models\Category;
use App\Models\JobApplication;
use App\Models\JobListing;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JobListingController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = JobListing::with(['category', 'poster'])
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

        // Additional filters like events page
        if ($request->filled('salary_range')) {
            switch ($request->salary_range) {
                case 'under_50k':
                    $query->where('salary_max', '<', 50000);
                    break;
                case '50k_100k':
                    $query->where(function ($q) {
                        $q->whereBetween('salary_min', [50000, 100000])
                            ->orWhereBetween('salary_max', [50000, 100000]);
                    });
                    break;
                case '100k_150k':
                    $query->where(function ($q) {
                        $q->whereBetween('salary_min', [100000, 150000])
                            ->orWhereBetween('salary_max', [100000, 150000]);
                    });
                    break;
                case 'over_150k':
                    $query->where('salary_min', '>', 150000);
                    break;
            }
        }

        if ($request->filled('date_posted')) {
            switch ($request->date_posted) {
                case 'today':
                    $query->whereDate('created_at', today());
                    break;
                case 'this_week':
                    $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'this_month':
                    $query->whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year);
                    break;
                case 'last_month':
                    $query->whereMonth('created_at', now()->subMonth()->month)
                        ->whereYear('created_at', now()->subMonth()->year);
                    break;
            }
        }

        if ($request->filled('company')) {
            $query->where('company_name', 'like', "%{$request->company}%");
        }

        $jobs = $query->paginate(12);

        $categories = Category::getCachedActive();
        $experienceLevels = ExperienceLevel::options();
        $locationTypes = LocationType::options();

        // Get user's job applications if authenticated
        $userApplications = collect();
        if (Auth::check()) {
            $userApplications = JobApplication::with(['jobListing:id,title,company_name'])
                ->where('user_id', Auth::id())
                ->latest()
                ->limit(5)
                ->get();
        }

        return view('jobs.index', compact('jobs', 'categories', 'experienceLevels', 'locationTypes', 'userApplications'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', JobListing::class);

        $categories = Category::getCachedActive();
        $experienceLevels = ExperienceLevel::options();
        $locationTypes = LocationType::options();

        return view('jobs.create', compact('categories', 'experienceLevels', 'locationTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', JobListing::class);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'company_name' => 'required|string|max:255',
            'company_description' => 'nullable|string',
            'location_type' => 'required|in:'.implode(',', array_column(LocationType::cases(), 'value')),
            'location' => 'required_unless:location_type,remote|string|max:255',
            'salary_min' => 'nullable|numeric|min:0',
            'salary_max' => 'nullable|numeric|min:0|gte:salary_min',
            'required_skills' => 'required|array|min:1',
            'required_skills.*' => 'string|max:50',
            'experience_level' => 'required|in:'.implode(',', array_column(ExperienceLevel::cases(), 'value')),
            'application_deadline' => 'required|date|after:today',
            'category_id' => 'required|exists:categories,id',
        ]);

        $validated['posted_by'] = Auth::id();
        $validated['status'] = 'active';

        $job = JobListing::create($validated);

        return redirect()->route('jobs.show', $job)
            ->with('success', 'Job listing created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(JobListing $job)
    {
        $job->load(['category', 'poster', 'applications.user']);

        $userApplication = null;
        if (Auth::check()) {
            $userApplication = $job->getUserApplication(Auth::user());
        }

        // Get recent applications for the sidebar (max 5)
        $recentApplications = $job->applications()
            ->with('user')
            ->latest()
            ->take(5)
            ->get();

        return view('jobs.show', compact('job', 'userApplication', 'recentApplications'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(JobListing $job)
    {
        $this->authorize('update', $job);

        $categories = Category::getCachedActive();
        $experienceLevels = ExperienceLevel::options();
        $locationTypes = LocationType::options();

        return view('jobs.edit', compact('job', 'categories', 'experienceLevels', 'locationTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, JobListing $job)
    {
        $this->authorize('update', $job);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'company_name' => 'required|string|max:255',
            'company_description' => 'nullable|string',
            'location_type' => 'required|in:'.implode(',', array_column(LocationType::cases(), 'value')),
            'location' => 'required_unless:location_type,remote|string|max:255',
            'salary_min' => 'nullable|numeric|min:0',
            'salary_max' => 'nullable|numeric|min:0|gte:salary_min',
            'required_skills' => 'required|array|min:1',
            'required_skills.*' => 'string|max:50',
            'experience_level' => 'required|in:'.implode(',', array_column(ExperienceLevel::cases(), 'value')),
            'application_deadline' => 'required|date|after:today',
            'category_id' => 'required|exists:categories,id',
            'status' => 'required|in:active,closed,archived',
        ]);

        $job->update($validated);

        return redirect()->route('jobs.show', $job)
            ->with('success', 'Job listing updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(JobListing $job)
    {
        $this->authorize('delete', $job);

        $job->delete();

        return redirect()->route('jobs.index')
            ->with('success', 'Job listing deleted successfully!');
    }

    /**
     * Apply for a job.
     */
    public function apply(Request $request, JobListing $job)
    {
        \Log::info('Job application attempt', [
            'user_id' => Auth::id(),
            'job_id' => $job->id,
            'can_apply' => $job->canUserApply(Auth::user()),
        ]);

        if (! $job->canUserApply(Auth::user())) {
            $reason = 'Unknown reason';
            if (! $job->isAcceptingApplications()) {
                if ($job->status !== \App\JobStatus::ACTIVE->value) {
                    $reason = 'Job is not active';
                } elseif ($job->hasDeadlinePassed()) {
                    $reason = 'Application deadline has passed';
                } else {
                    $reason = 'Job is not accepting applications';
                }
            } elseif ($job->applications()->where('user_id', Auth::id())->exists()) {
                $reason = 'You have already applied for this job';
            }

            return redirect()->back()
                ->withErrors(['error' => 'You cannot apply for this job. '.$reason])
                ->withInput();
        }

        $validated = $request->validate([
            'cover_letter' => 'required|string|max:2000',
            'additional_info' => 'nullable|string|max:1000',
        ]);

        \Log::info('Validation passed', ['validated' => $validated]);

        try {
            $application = $job->applyForJob(Auth::user(), $validated);

            \Log::info('Application created successfully', [
                'application_id' => $application->id,
                'status' => $application->status->value ?? 'unknown',
            ]);

            return redirect()->route('jobs.show', $job)
                ->with('success', 'Application submitted successfully!');
        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Job application database error', [
                'user_id' => Auth::id(),
                'job_id' => $job->id,
                'error' => $e->getMessage(),
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
            ]);

            // Check if it's a duplicate entry error
            if ($e->getCode() === '23000' || str_contains($e->getMessage(), 'Duplicate entry')) {
                return redirect()->back()
                    ->withErrors(['error' => 'You have already applied for this job.'])
                    ->withInput();
            }

            return redirect()->back()
                ->withErrors(['error' => 'Failed to submit application. Please try again.'])
                ->withInput();
        } catch (\Exception $e) {
            \Log::error('Job application failed', [
                'user_id' => Auth::id(),
                'job_id' => $job->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Failed to submit application: '.$e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Close a job listing.
     */
    public function close(JobListing $job)
    {
        $this->authorize('update', $job);

        $job->close();

        return redirect()->back()
            ->with('success', 'Job listing closed successfully!');
    }

    /**
     * Reopen a job listing.
     */
    public function reopen(JobListing $job)
    {
        $this->authorize('update', $job);

        $job->reopen();

        return redirect()->back()
            ->with('success', 'Job listing reopened successfully!');
    }

    /**
     * Archive a job listing.
     */
    public function archive(JobListing $job)
    {
        $this->authorize('update', $job);

        $job->archive();

        return redirect()->back()
            ->with('success', 'Job listing archived successfully!');
    }

    /**
     * Show current user's job applications.
     */
    public function myApplications(Request $request)
    {
        $user = Auth::user();

        // If user is admin, redirect them (admins shouldn't use this page)
        if ($user->isAdmin()) {
            return redirect()->route('jobs.index');
        }

        $query = JobApplication::with(['jobListing:id,title,company_name'])
            ->where('user_id', $user->id)
            ->latest();

        // Filter by status if provided
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $applications = $query->paginate(15);

        return view('jobs.my-applications', compact('applications'));
    }

    /**
     * Show a single user's application.
     */
    public function showMyApplication(JobApplication $application)
    {
        $user = Auth::user();

        // If user is admin, redirect them
        if ($user->isAdmin()) {
            return redirect()->route('jobs.index');
        }

        // Ensure the application belongs to the current user
        if ($application->user_id !== $user->id) {
            abort(403, 'Unauthorized access to application.');
        }

        // Load the job listing with related data
        $application->load(['jobListing.category', 'jobListing.poster']);

        return view('jobs.show-my-application', compact('application'));
    }

    /**
     * Show applications for a job (for job poster).
     */
    public function applications(JobListing $job)
    {
        $this->authorize('view-applications', $job);

        $applications = $job->applications()
            ->with('user')
            ->latest()
            ->paginate(20);

        return view('jobs.applications', compact('job', 'applications'));
    }

    /**
     * Review an application.
     */
    public function reviewApplication(Request $request, JobApplication $application)
    {
        $this->authorize('view-applications', $application->jobListing);

        $application->update([
            'status' => 'reviewed',
            'status_updated_at' => now(),
        ]);

        return redirect()->back()
            ->with('success', 'Application marked as reviewed.');
    }

    /**
     * Accept an application.
     */
    public function acceptApplication(Request $request, JobApplication $application)
    {
        $this->authorize('view-applications', $application->jobListing);

        $application->update([
            'status' => 'accepted',
            'status_updated_at' => now(),
        ]);

        return redirect()->back()
            ->with('success', 'Application accepted.');
    }

    /**
     * Reject an application.
     */
    public function rejectApplication(Request $request, JobApplication $application)
    {
        $this->authorize('view-applications', $application->jobListing);

        $application->update([
            'status' => 'rejected',
            'status_updated_at' => now(),
        ]);

        return redirect()->back()
            ->with('success', 'Application rejected.');
    }

    /**
     * Update application notes.
     */
    public function updateApplicationNotes(Request $request, JobApplication $application)
    {
        $this->authorize('view-applications', $application->jobListing);

        $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $application->update([
            'admin_notes' => $request->admin_notes,
        ]);

        return redirect()->back()
            ->with('success', 'Application notes updated.');
    }
}
