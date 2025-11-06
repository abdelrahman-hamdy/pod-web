<?php

namespace App\Http\Controllers\Api;

use App\HackathonFormat;
use App\Http\Resources\HackathonResource;
use App\Models\Hackathon;
use App\Models\HackathonTeam;
use App\Models\HackathonTeamInvitation;
use App\Models\HackathonTeamJoinRequest;
use App\SkillLevel;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HackathonController extends BaseApiController
{
    use AuthorizesRequests;

    /**
     * Display a listing of hackathons.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Hackathon::with(['creator', 'category'])
            ->latest();

        // Apply filters
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('status')) {
            match ($request->status) {
                'upcoming' => $query->upcoming(),
                'ongoing' => $query->ongoing(),
                'past' => $query->past(),
                'registration_open' => $query->acceptingRegistrations(),
                default => null,
            };
        }

        if ($request->filled('skill_level')) {
            $query->where('skill_requirements', $request->skill_level);
        }

        if ($request->filled('prize_range')) {
            match ($request->prize_range) {
                '0-10' => $query->whereBetween('prize_pool', [0, 10000]),
                '10-50' => $query->whereBetween('prize_pool', [10000, 50000]),
                '50-100' => $query->whereBetween('prize_pool', [50000, 100000]),
                '100+' => $query->where('prize_pool', '>', 100000),
                'free' => $query->where(function ($q) {
                    $q->where('prize_pool', 0)->orWhereNull('prize_pool');
                }),
                default => null,
            };
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $hackathons = $query->paginate($request->get('per_page', 6));

        return $this->paginatedResponse($hackathons);
    }

    /**
     * Store a newly created hackathon.
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Hackathon::class);

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
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validated['format'] === 'online' && empty($validated['location'])) {
            $validated['location'] = null;
        }

        $validated['created_by'] = $request->user()->id;
        $validated['is_active'] = true;
        $validated['entry_fee'] = 0;

        // Handle cover image upload
        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = $request->file('cover_image')
                ->store('hackathons/covers', 'public');
        }

        $hackathon = Hackathon::create($validated);
        $hackathon->load(['creator', 'category']);

        return $this->successResponse(new HackathonResource($hackathon), 'Hackathon created successfully', 201);
    }

    /**
     * Display the specified hackathon.
     */
    public function show(Hackathon $hackathon): JsonResponse
    {
        $hackathon->load(['creator', 'category']);

        return $this->successResponse(new HackathonResource($hackathon));
    }

    /**
     * Update the specified hackathon.
     */
    public function update(Request $request, Hackathon $hackathon): JsonResponse
    {
        $this->authorize('update', $hackathon);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after:start_date',
            'registration_deadline' => 'sometimes|required|date|before:start_date',
            'max_participants' => 'nullable|integer|min:1',
            'max_team_size' => 'sometimes|required|integer|min:1|max:10',
            'min_team_size' => 'sometimes|required|integer|min:1|lte:max_team_size',
            'prize_pool' => 'nullable|numeric|min:0',
            'location' => 'nullable|string|max:255',
            'format' => 'sometimes|required|in:'.implode(',', array_column(HackathonFormat::cases(), 'value')),
            'skill_requirements' => 'nullable|in:'.implode(',', array_column(SkillLevel::cases(), 'value')),
            'technologies' => 'nullable|array',
            'technologies.*' => 'string|max:255',
            'rules' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validated['format'] === 'online' && empty($validated['location'])) {
            $validated['location'] = null;
        }

        $validated['entry_fee'] = 0;
        $validated['is_active'] = $request->has('is_active') ? true : false;

        // Handle cover image upload
        if ($request->hasFile('cover_image')) {
            if ($hackathon->cover_image) {
                Storage::disk('public')->delete($hackathon->cover_image);
            }

            $validated['cover_image'] = $request->file('cover_image')
                ->store('hackathons/covers', 'public');
        }

        $hackathon->update($validated);
        $hackathon->load(['creator', 'category']);

        return $this->successResponse(new HackathonResource($hackathon), 'Hackathon updated successfully');
    }

    /**
     * Remove the specified hackathon.
     */
    public function destroy(Request $request, Hackathon $hackathon): JsonResponse
    {
        $this->authorize('delete', $hackathon);

        if ($hackathon->cover_image) {
            Storage::disk('public')->delete($hackathon->cover_image);
        }

        $hackathon->delete();

        return $this->successResponse(null, 'Hackathon deleted successfully', 204);
    }

    /**
     * Register for a hackathon.
     */
    public function register(Request $request, Hackathon $hackathon): JsonResponse
    {
        if (! $hackathon->canUserParticipate($request->user())) {
            return $this->errorResponse('You cannot participate in this hackathon', null, 400);
        }

        // Registration logic handled by joinTeam or createTeam
        return $this->successResponse(null, 'Registration successful');
    }

    /**
     * Get hackathon teams.
     */
    public function teams(Request $request, Hackathon $hackathon): JsonResponse
    {
        $teams = $hackathon->teams()
            ->with(['leader', 'members.user'])
            ->latest()
            ->paginate($request->get('per_page', 20));

        return $this->paginatedResponse($teams);
    }

    /**
     * Join a team for a hackathon.
     */
    public function joinTeam(Request $request, Hackathon $hackathon): JsonResponse
    {
        if (! $hackathon->canUserParticipate($request->user())) {
            return $this->errorResponse('You cannot participate in this hackathon', null, 400);
        }

        $validated = $request->validate([
            'team_id' => 'required|exists:hackathon_teams,id',
        ]);

        $team = HackathonTeam::findOrFail($validated['team_id']);

        if ($team->hackathon_id !== $hackathon->id) {
            return $this->errorResponse('Team does not belong to this hackathon', null, 400);
        }

        if (! $team->hasAvailableSpots()) {
            return $this->errorResponse('Team is full', null, 400);
        }

        if (! $team->is_public) {
            return $this->errorResponse('Team is not accepting join requests', null, 400);
        }

        // Create join request
        $joinRequest = HackathonTeamJoinRequest::create([
            'team_id' => $team->id,
            'user_id' => $request->user()->id,
            'status' => 'pending',
        ]);

        return $this->successResponse(['join_request_id' => $joinRequest->id], 'Join request sent successfully', 201);
    }

    /**
     * Leave a team.
     */
    public function leaveTeam(Request $request, Hackathon $hackathon): JsonResponse
    {
        $validated = $request->validate([
            'team_id' => 'required|exists:hackathon_teams,id',
        ]);

        $team = HackathonTeam::findOrFail($validated['team_id']);

        if ($team->hackathon_id !== $hackathon->id) {
            return $this->errorResponse('Team does not belong to this hackathon', null, 400);
        }

        if ($team->leader_id === $request->user()->id) {
            return $this->errorResponse('Team leaders cannot leave. Transfer leadership or disband the team first', null, 400);
        }

        $team->removeMember($request->user());

        return $this->successResponse(null, 'Successfully left the team', 204);
    }

    /**
     * Get user's teams.
     */
    public function myTeams(Request $request): JsonResponse
    {
        $teams = HackathonTeam::whereHas('hackathon', function ($q) {
            $q->where('start_date', '>', now());
        })
            ->where(function ($query) use ($request) {
                $query->where('leader_id', $request->user()->id)
                    ->orWhereHas('members', function ($q) use ($request) {
                        $q->where('user_id', $request->user()->id);
                    });
            })
            ->with(['hackathon', 'leader', 'members.user'])
            ->latest()
            ->paginate($request->get('per_page', 15));

        return $this->paginatedResponse($teams);
    }

    /**
     * Get available teams (teams that can be joined).
     */
    public function availableTeams(Request $request): JsonResponse
    {
        $teams = HackathonTeam::whereHas('hackathon', function ($q) {
            $q->where('start_date', '>', now())
              ->where('registration_deadline', '>', now());
        })
            ->whereDoesntHave('members', function ($q) use ($request) {
                $q->where('user_id', $request->user()->id);
            })
            ->where('leader_id', '!=', $request->user()->id)
            ->with(['hackathon', 'leader', 'members.user'])
            ->latest()
            ->paginate($request->get('per_page', 15));

        return $this->paginatedResponse($teams);
    }

    /**
     * Get user's received invitations.
     */
    public function receivedInvitations(Request $request): JsonResponse
    {
        $invitations = HackathonTeamInvitation::where('invitee_id', $request->user()->id)
            ->where('status', 'pending')
            ->with(['team.hackathon', 'team.leader', 'inviter'])
            ->latest()
            ->paginate($request->get('per_page', 15));

        return $this->paginatedResponse($invitations);
    }

    /**
     * Get user's sent join requests.
     */
    public function sentJoinRequests(Request $request): JsonResponse
    {
        $requests = HackathonTeamJoinRequest::where('user_id', $request->user()->id)
            ->where('status', 'pending')
            ->with(['team.hackathon', 'team.leader'])
            ->latest()
            ->paginate($request->get('per_page', 15));

        return $this->paginatedResponse($requests);
    }

    /**
     * Get join requests received by user's teams.
     */
    public function receivedJoinRequests(Request $request): JsonResponse
    {
        $requests = HackathonTeamJoinRequest::whereHas('team', function ($q) use ($request) {
            $q->where('leader_id', $request->user()->id);
        })
            ->where('status', 'pending')
            ->with(['team.hackathon', 'user'])
            ->latest()
            ->paginate($request->get('per_page', 15));

        return $this->paginatedResponse($requests);
    }

    /**
     * Create a team.
     */
    public function createTeam(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'hackathon_id' => 'required|exists:hackathons,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $hackathon = Hackathon::findOrFail($validated['hackathon_id']);

        if (! $hackathon->isRegistrationOpen()) {
            return $this->errorResponse('Registration is closed for this hackathon', null, 400);
        }

        if (! $hackathon->canUserParticipate($request->user())) {
            return $this->errorResponse('You cannot participate in this hackathon', null, 400);
        }

        if (HackathonTeam::where('hackathon_id', $hackathon->id)
            ->where('name', $validated['name'])
            ->exists()) {
            return $this->errorResponse('Team name already exists for this hackathon', null, 422);
        }

        $team = HackathonTeam::create([
            'hackathon_id' => $hackathon->id,
            'name' => $validated['name'],
            'leader_id' => $request->user()->id,
            'description' => $validated['description'] ?? null,
            'is_public' => $request->get('is_public', true),
        ]);

        $team->load(['hackathon', 'leader', 'members']);

        return $this->successResponse($team, 'Team created successfully', 201);
    }

    /**
     * Update a team.
     */
    public function updateTeam(Request $request, HackathonTeam $team): JsonResponse
    {
        if ($team->leader_id !== $request->user()->id) {
            return $this->forbiddenResponse('Only team leaders can update team settings');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_public' => 'boolean',
        ]);

        $team->update($validated);

        return $this->successResponse($team->fresh(['leader', 'members.user']), 'Team updated successfully');
    }

    /**
     * Delete a team.
     */
    public function deleteTeam(Request $request, HackathonTeam $team): JsonResponse
    {
        if ($team->leader_id !== $request->user()->id) {
            return $this->forbiddenResponse('Only team leaders can delete the team');
        }

        if ($team->hackathon->start_date <= now()) {
            return $this->errorResponse('Cannot delete team. The hackathon has already started', null, 400);
        }

        // Remove all members
        $team->members()->delete();

        // Delete invitations and join requests
        HackathonTeamInvitation::where('team_id', $team->id)->delete();
        HackathonTeamJoinRequest::where('team_id', $team->id)->delete();

        $team->delete();

        return $this->successResponse(null, 'Team deleted successfully', 204);
    }

    /**
     * Invite member to team.
     */
    public function inviteMember(Request $request, HackathonTeam $team): JsonResponse
    {
        if ($team->leader_id !== $request->user()->id) {
            return $this->forbiddenResponse('Only team leaders can invite members');
        }

        $validated = $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'required|exists:users,id',
            'message' => 'nullable|string|max:500',
        ]);

        $currentMembers = $team->members->count();
        $availableSpots = $team->hackathon->max_team_size - $currentMembers;

        if (count($validated['user_ids']) > $availableSpots) {
            return $this->errorResponse("You can only invite up to {$availableSpots} more member(s)", null, 400);
        }

        $invited = [];
        $errors = [];

        foreach ($validated['user_ids'] as $userId) {
            $invitee = \App\Models\User::findOrFail($userId);

            if ($team->hasUser($invitee)) {
                $errors[] = "{$invitee->name} is already a member";

                continue;
            }

            if (HackathonTeamInvitation::where('team_id', $team->id)
                ->where('invitee_id', $userId)
                ->where('status', 'pending')
                ->exists()) {
                $errors[] = "Invitation already sent to {$invitee->name}";

                continue;
            }

            $invitation = HackathonTeamInvitation::create([
                'team_id' => $team->id,
                'inviter_id' => $request->user()->id,
                'invitee_id' => $userId,
                'message' => $validated['message'] ?? null,
                'status' => 'pending',
            ]);

            $invited[] = $invitation->id;
        }

        if (empty($invited) && ! empty($errors)) {
            return $this->validationErrorResponse($errors);
        }

        return $this->successResponse([
            'invited' => $invited,
            'errors' => $errors,
        ], count($invited).' invitation(s) sent successfully');
    }

    /**
     * Request to join a team.
     */
    public function requestToJoin(Request $request, HackathonTeam $team): JsonResponse
    {
        if (! $team->is_public) {
            return $this->errorResponse('Team is not accepting join requests', null, 400);
        }

        if (! $team->hasAvailableSpots()) {
            return $this->errorResponse('Team is full', null, 400);
        }

        if ($team->hasUser($request->user())) {
            return $this->errorResponse('You are already a member of this team', null, 400);
        }

        if (HackathonTeamJoinRequest::where('team_id', $team->id)
            ->where('user_id', $request->user()->id)
            ->where('status', 'pending')
            ->exists()) {
            return $this->errorResponse('You have already sent a join request to this team', null, 400);
        }

        $joinRequest = HackathonTeamJoinRequest::create([
            'team_id' => $team->id,
            'user_id' => $request->user()->id,
            'status' => 'pending',
        ]);

        return $this->successResponse(['join_request_id' => $joinRequest->id], 'Join request sent successfully', 201);
    }

    /**
     * Accept invitation.
     */
    public function acceptInvitation(Request $request, HackathonTeamInvitation $invitation): JsonResponse
    {
        if ($invitation->invitee_id !== $request->user()->id) {
            return $this->forbiddenResponse();
        }

        if ($invitation->status !== 'pending') {
            return $this->errorResponse('Invitation has already been responded to', null, 400);
        }

        $team = $invitation->team;

        if (! $team->hasAvailableSpots()) {
            return $this->errorResponse('Team is now full', null, 400);
        }

        if (! $team->hackathon->canUserParticipate($request->user())) {
            return $this->errorResponse('You cannot participate in this hackathon', null, 400);
        }

        $team->addMember($request->user());

        $invitation->update([
            'status' => 'accepted',
            'responded_at' => now(),
        ]);

        return $this->successResponse(null, 'You have joined the team!');
    }

    /**
     * Reject invitation.
     */
    public function rejectInvitation(Request $request, HackathonTeamInvitation $invitation): JsonResponse
    {
        if ($invitation->invitee_id !== $request->user()->id) {
            return $this->forbiddenResponse();
        }

        if ($invitation->status !== 'pending') {
            return $this->errorResponse('Invitation has already been responded to', null, 400);
        }

        $invitation->update([
            'status' => 'rejected',
            'responded_at' => now(),
        ]);

        return $this->successResponse(null, 'Invitation rejected', 204);
    }

    /**
     * Accept join request.
     */
    public function acceptJoinRequest(Request $request, HackathonTeamJoinRequest $joinRequest): JsonResponse
    {
        $team = $joinRequest->team;

        if ($team->leader_id !== $request->user()->id) {
            return $this->forbiddenResponse('Only team leaders can accept join requests');
        }

        if ($joinRequest->status !== 'pending') {
            return $this->errorResponse('Join request has already been responded to', null, 400);
        }

        if (! $team->hasAvailableSpots()) {
            return $this->errorResponse('Team is now full', null, 400);
        }

        $team->addMember($joinRequest->user);

        $joinRequest->update([
            'status' => 'accepted',
            'responded_at' => now(),
        ]);

        return $this->successResponse(null, 'Join request accepted');
    }

    /**
     * Reject join request.
     */
    public function rejectJoinRequest(Request $request, HackathonTeamJoinRequest $joinRequest): JsonResponse
    {
        $team = $joinRequest->team;

        if ($team->leader_id !== $request->user()->id) {
            return $this->forbiddenResponse('Only team leaders can reject join requests');
        }

        if ($joinRequest->status !== 'pending') {
            return $this->errorResponse('Join request has already been responded to', null, 400);
        }

        $joinRequest->update([
            'status' => 'rejected',
            'responded_at' => now(),
        ]);

        return $this->successResponse(null, 'Join request rejected', 204);
    }
}
