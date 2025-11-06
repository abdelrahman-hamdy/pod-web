<?php

namespace App\Http\Controllers;

use App\HackathonFormat;
use App\Models\Hackathon;
use App\Models\HackathonCategory;
use App\Models\HackathonTeam;
use App\SkillLevel;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class HackathonController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Hackathon::with(['creator', 'category'])
            ->latest();

        // Apply filters
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('status')) {
            switch ($request->status) {
                case 'upcoming':
                    $query->upcoming();
                    break;
                case 'ongoing':
                    $query->ongoing();
                    break;
                case 'past':
                    $query->past();
                    break;
                case 'registration_open':
                    $query->acceptingRegistrations();
                    break;
            }
        }

        // Skill level filter
        if ($request->filled('skill_level')) {
            $query->where('skill_requirements', $request->skill_level);
        }

        // Prize range filter
        if ($request->filled('prize_range')) {
            switch ($request->prize_range) {
                case '0-10':
                    $query->whereBetween('prize_pool', [0, 10000]);
                    break;
                case '10-50':
                    $query->whereBetween('prize_pool', [10000, 50000]);
                    break;
                case '50-100':
                    $query->whereBetween('prize_pool', [50000, 100000]);
                    break;
                case '100+':
                    $query->where('prize_pool', '>', 100000);
                    break;
                case 'free':
                    $query->where('prize_pool', 0)->orWhereNull('prize_pool');
                    break;
            }
        }

        // Search filter
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $hackathons = $query->paginate(6);

        // Get filter options
        $skillLevels = SkillLevel::getOptions();
        $categories = HackathonCategory::active()->ordered()->get();

        return view('hackathons.index', compact('hackathons', 'skillLevels', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Hackathon::class);

        $formats = HackathonFormat::getOptions();
        $skillLevels = SkillLevel::getOptions();
        $technologies = ['JavaScript', 'Python', 'React', 'Node.js', 'AI/ML', 'Blockchain', 'Mobile', 'Web', 'Data Science'];
        $categories = HackathonCategory::active()->ordered()->get();
        $isEdit = false;
        $hackathon = null;

        return view('hackathons.form', compact('formats', 'skillLevels', 'technologies', 'categories', 'isEdit', 'hackathon'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Hackathon::class);

        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'start_date' => 'required|date|after:now',
                'end_date' => 'required|date|after:start_date',
                'registration_deadline' => 'required|date|after:now|before:start_date',
                'max_participants' => 'nullable|integer|min:1',
                'max_team_size' => 'required|integer|min:1|max:10',
                'min_team_size' => 'required|integer|min:1|lte:max_team_size',
                'prize_pool' => 'nullable|numeric|min:0',
                'location' => 'nullable|string|max:255|required_if:format,on-site,hybrid',
                'format' => 'required|in:'.implode(',', array_column(HackathonFormat::cases(), 'value')),
                'skill_requirements' => 'nullable|in:'.implode(',', array_column(SkillLevel::cases(), 'value')),
                'technologies' => 'nullable|array',
                'technologies.*' => 'string|max:255',
                'rules' => 'nullable|string',
                'category_id' => 'nullable|exists:hackathon_categories,id',
                'banner_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            // Set location to null if format is online and location is empty
            if ($validated['format'] === 'online' && empty($validated['location'])) {
                $validated['location'] = null;
            }

            $validated['created_by'] = Auth::id();
            $validated['is_active'] = true;
            $validated['entry_fee'] = 0; // Set default entry fee (hidden field)

            // Handle banner image upload
            if ($request->hasFile('banner_image')) {
                $validated['banner_image'] = $request->file('banner_image')
                    ->store('hackathons', 'public');
            }

            $hackathon = Hackathon::create($validated);

            return redirect()->route('hackathons.show', $hackathon)
                ->with('success', 'Hackathon created successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create hackathon: '.$e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Hackathon $hackathon)
    {
        $hackathon->load(['creator', 'teams.leader', 'teams.members']);

        $userTeam = null;
        if (Auth::check()) {
            $userTeam = $hackathon->teams()
                ->where(function ($query) {
                    $query->where('leader_id', Auth::id())
                        ->orWhereHas('members', function ($q) {
                            $q->where('user_id', Auth::id());
                        });
                })
                ->with(['leader', 'members.user'])
                ->first();
        }

        $availableSpots = $hackathon->getAvailableSpots();
        $isRegistrationOpen = $hackathon->isRegistrationOpen();
        $hackathonStatus = $hackathon->getStatus();

        return view('hackathons.show', compact(
            'hackathon',
            'userTeam',
            'availableSpots',
            'isRegistrationOpen',
            'hackathonStatus'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Hackathon $hackathon)
    {
        $this->authorize('update', $hackathon);

        $formats = HackathonFormat::getOptions();
        $skillLevels = SkillLevel::getOptions();
        $technologies = ['JavaScript', 'Python', 'React', 'Node.js', 'AI/ML', 'Blockchain', 'Mobile', 'Web', 'Data Science'];
        $categories = HackathonCategory::active()->ordered()->get();
        $isEdit = true;

        return view('hackathons.form', compact('hackathon', 'formats', 'skillLevels', 'technologies', 'categories', 'isEdit'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Hackathon $hackathon)
    {
        $this->authorize('update', $hackathon);

        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
                'registration_deadline' => 'required|date|before:start_date',
                'max_participants' => 'nullable|integer|min:1',
                'max_team_size' => 'required|integer|min:1|max:10',
                'min_team_size' => 'required|integer|min:1|lte:max_team_size',
                'prize_pool' => 'nullable|numeric|min:0',
                'location' => 'nullable|string|max:255|required_if:format,on-site,hybrid',
                'format' => 'required|in:'.implode(',', array_column(HackathonFormat::cases(), 'value')),
                'skill_requirements' => 'nullable|in:'.implode(',', array_column(SkillLevel::cases(), 'value')),
                'technologies' => 'nullable|array',
                'technologies.*' => 'string|max:255',
                'rules' => 'nullable|string',
                'is_active' => 'sometimes|boolean',
                'banner_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            // Set location to null if format is online and location is empty
            if ($validated['format'] === 'online' && empty($validated['location'])) {
                $validated['location'] = null;
            }

            $validated['entry_fee'] = 0; // Set default entry fee (hidden field)

            // Handle checkbox - if not checked, set to false
            $validated['is_active'] = $request->has('is_active') ? true : false;

            // Handle banner image upload
            if ($request->hasFile('banner_image')) {
                // Delete old banner image
                if ($hackathon->banner_image) {
                    Storage::disk('public')->delete($hackathon->banner_image);
                }

                $validated['banner_image'] = $request->file('banner_image')
                    ->store('hackathons', 'public');
            }

            $hackathon->update($validated);

            return redirect()->route('hackathons.show', $hackathon)
                ->with('success', 'Hackathon updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update hackathon: '.$e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Hackathon $hackathon)
    {
        $this->authorize('delete', $hackathon);

        // Delete banner image if exists
        if ($hackathon->banner_image) {
            Storage::disk('public')->delete($hackathon->banner_image);
        }

        $hackathon->delete();

        return redirect()->route('hackathons.index')
            ->with('success', 'Hackathon deleted successfully!');
    }

    /**
     * Create or join a team for a hackathon.
     */
    public function joinTeam(Request $request, Hackathon $hackathon)
    {
        if (! $hackathon->canUserParticipate(Auth::user())) {
            return redirect()->back()
                ->with('error', 'You cannot participate in this hackathon.');
        }

        $validated = $request->validate([
            'team_name' => 'required_without:team_id|string|max:255',
            'team_id' => 'required_without:team_name|exists:hackathon_teams,id',
        ]);

        if (isset($validated['team_id'])) {
            // Join existing team
            $team = HackathonTeam::findOrFail($validated['team_id']);

            if ($team->hackathon_id !== $hackathon->id) {
                return redirect()->back()
                    ->with('error', 'Invalid team.');
            }

            if ($team->members()->count() >= $hackathon->max_team_size - 1) {
                return redirect()->back()
                    ->with('error', 'Team is full.');
            }

            $team->members()->create(['user_id' => Auth::id()]);

            return redirect()->route('hackathons.show', $hackathon)
                ->with('success', 'Successfully joined team!');
        } else {
            // Create new team
            $team = $hackathon->teams()->create([
                'name' => $validated['team_name'],
                'leader_id' => Auth::id(),
            ]);

            return redirect()->route('hackathons.show', $hackathon)
                ->with('success', 'Team created successfully!');
        }
    }

    /**
     * Leave a team.
     */
    public function leaveTeam(Hackathon $hackathon)
    {
        $userTeam = $hackathon->teams()
            ->where(function ($query) {
                $query->where('leader_id', Auth::id())
                    ->orWhereHas('members', function ($q) {
                        $q->where('user_id', Auth::id());
                    });
            })
            ->first();

        if (! $userTeam) {
            return redirect()->back()
                ->with('error', 'You are not part of any team.');
        }

        if ($userTeam->leader_id === Auth::id()) {
            // If user is team leader, delete the team
            $userTeam->delete();
            $message = 'Team disbanded successfully.';
        } else {
            // Remove user from team
            $userTeam->members()->where('user_id', Auth::id())->delete();
            $message = 'Left team successfully.';
        }

        return redirect()->route('hackathons.show', $hackathon)
            ->with('success', $message);
    }

    /**
     * Register user for hackathon by creating or joining a team.
     */
    public function register(Request $request, Hackathon $hackathon)
    {
        $user = Auth::user();

        // Check if user can participate
        if (! $hackathon->canUserParticipate($user)) {
            return back()->with('error', 'You cannot participate in this hackathon.');
        }

        $request->validate([
            'action' => 'required|in:create_team,join_team',
            'team_name' => 'required_if:action,create_team|string|max:255',
            'team_description' => 'nullable|string',
            'team_id' => 'required_if:action,join_team|exists:hackathon_teams,id',
        ]);

        if ($request->action === 'create_team') {
            // Check if team name is unique for this hackathon
            if (HackathonTeam::where('hackathon_id', $hackathon->id)
                ->where('name', $request->team_name)
                ->exists()) {
                return back()->with('error', 'Team name already exists for this hackathon.');
            }

            // Create team
            $team = HackathonTeam::create([
                'hackathon_id' => $hackathon->id,
                'name' => $request->team_name,
                'leader_id' => $user->id,
                'description' => $request->team_description,
                'is_public' => $hackathon->min_team_size > 1,
            ]);

            return back()->with('success', 'Team created successfully! You are now registered for the hackathon.');
        } else {
            // Join existing team
            $team = HackathonTeam::findOrFail($request->team_id);

            // Check if team has available spots
            if (! $team->hasAvailableSpots()) {
                return back()->with('error', 'Team is full.');
            }

            try {
                $team->addMember($user);

                return back()->with('success', 'Successfully joined the team! You are now registered for the hackathon.');
            } catch (\Exception $e) {
                return back()->with('error', $e->getMessage());
            }
        }
    }

    /**
     * Show teams for a hackathon.
     */
    public function teams(Hackathon $hackathon)
    {
        $teams = $hackathon->teams()
            ->with(['leader', 'members.user'])
            ->latest()
            ->paginate(20);

        return view('hackathons.teams', compact('hackathon', 'teams'));
    }

    /**
     * Show hackathon participants (for organizers).
     */
    public function participants(Hackathon $hackathon)
    {
        $this->authorize('view-participants', $hackathon);

        $teams = $hackathon->teams()
            ->with(['leader', 'members.user'])
            ->latest()
            ->paginate(50);

        return view('hackathons.participants', compact('hackathon', 'teams'));
    }

    /**
     * Export hackathon participants.
     */
    public function exportParticipants(Hackathon $hackathon)
    {
        $this->authorize('view-participants', $hackathon);

        $participants = [];

        foreach ($hackathon->teams()->with(['leader', 'members.user'])->get() as $team) {
            // Add team leader
            $participants[] = [
                'team_name' => $team->name,
                'name' => $team->leader->name,
                'email' => $team->leader->email,
                'role' => 'Team Leader',
                'joined_at' => $team->created_at->format('Y-m-d H:i:s'),
            ];

            // Add team members
            foreach ($team->members as $member) {
                $participants[] = [
                    'team_name' => $team->name,
                    'name' => $member->user->name,
                    'email' => $member->user->email,
                    'role' => 'Team Member',
                    'joined_at' => $member->created_at->format('Y-m-d H:i:s'),
                ];
            }
        }

        return response()->json($participants);
    }
}
