<?php

namespace App\Http\Controllers;

use App\Models\Internship;
use App\Models\InternshipCategory;
use Illuminate\Http\Request;

class InternshipController extends Controller
{
    public function index(Request $request)
    {
        $categories = InternshipCategory::all();

        $query = Internship::where('status', 'open')->with('category');

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        $internships = $query->orderBy('application_deadline', 'asc')->paginate(9);

        $myApplications = auth()->check()
            ? \App\Models\InternshipApplication::where('user_id', auth()->id())
                ->with('internship')
                ->latest()
                ->get()
            : collect();

        return view('internships.index', compact('internships', 'categories', 'myApplications'));
    }

    public function create()
    {
        $categories = InternshipCategory::all();

        return view('internships.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'company_name' => 'required|string|max:255',
            'category_id' => 'required|exists:internship_categories,id',
            'location' => 'required|string|max:255',
            'type' => 'required|in:full_time,part_time,remote,hybrid',
            'duration' => 'nullable|string|max:255',
            'application_deadline' => 'required|date|after:today',
            'start_date' => 'nullable|date|after:application_deadline',
        ]);

        $validated['status'] = 'open';

        $internship = Internship::create($validated);

        return redirect()->route('internships.show', $internship)
            ->with('success', 'Internship created successfully!');
    }

    public function show(Internship $internship)
    {
        return view('internships.show', compact('internship'));
    }

    public function myApplications()
    {
        $applications = \App\Models\InternshipApplication::where('user_id', auth()->id())
            ->with('internship')
            ->latest()
            ->get();

        return view('internships.my-applications', compact('applications'));
    }
}
