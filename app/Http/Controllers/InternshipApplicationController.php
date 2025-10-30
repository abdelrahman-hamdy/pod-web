<?php

namespace App\Http\Controllers;

use App\Models\InternshipApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InternshipApplicationController extends Controller
{
    /**
     * Display the internship application form.
     */
    public function create(Request $request)
    {
        $internship_id = $request->query('internship_id');
        $internship = null;

        if ($internship_id) {
            $internship = \App\Models\Internship::find($internship_id);
        }

        $categories = \App\Models\InternshipCategory::all();
        $graduationStatuses = \App\GraduationStatus::options();

        return view('internships.apply', compact('internship_id', 'internship', 'categories', 'graduationStatuses'));
    }

    /**
     * Store a newly created internship application.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'university' => 'nullable|string|max:255',
            'major' => 'nullable|string|max:255',
            'graduation_status' => 'nullable|in:student,graduating_soon,recent_graduate,graduated',
            'experience' => 'nullable|string|max:2000',
            'interest_categories' => 'required|array|min:1',
            'interest_categories.*' => 'exists:internship_categories,id',
            'availability_start' => 'required|date|after:today',
            'availability_end' => 'required|date|after:availability_start',
            'motivation' => 'required|string|max:2000',
            'terms' => 'accepted',
        ]);

        // Create the internship application
        $application = InternshipApplication::create([
            'internship_id' => $request->internship_id,
            'user_id' => Auth::id(),
            'full_name' => $validated['full_name'],
            'email' => Auth::user()->email, // Always use authenticated user's email
            'phone' => $validated['phone'],
            'university' => $validated['university'],
            'major' => $validated['major'],
            'graduation_status' => $validated['graduation_status'],
            'experience' => $validated['experience'],
            'interest_categories' => $validated['interest_categories'],
            'availability_start' => $validated['availability_start'],
            'availability_end' => $validated['availability_end'],
            'motivation' => $validated['motivation'],
            'status' => 'pending',
        ]);

        return redirect()->route('internships.index')->with('success', 'Your internship application has been submitted successfully! We will review your application and get back to you soon.');
    }

    /**
     * Display the internship index page.
     */
    public function index()
    {
        $internships = \App\Models\Internship::where('status', 'open')
            ->orderBy('application_deadline', 'asc')
            ->get();

        $myApplications = InternshipApplication::where('user_id', Auth::id())
            ->with('internship')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('internships.index', compact('internships', 'myApplications'));
    }

    /**
     * Display the specified internship application.
     */
    public function show(InternshipApplication $application)
    {
        // For future admin functionality
        return view('admin.internships.show', compact('application'));
    }
}
