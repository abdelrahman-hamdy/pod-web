<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\EventResource;
use App\Models\Event;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class EventController extends BaseApiController
{
    use AuthorizesRequests;

    /**
     * Display a listing of events.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Event::with(['creator', 'category'])
            ->active()
            ->latest();
            
        // Resolve user from request (supports optional bearer token)
        $currentUser = $this->resolveUser($request);
        // Load current user's registrations if authenticated
        if ($currentUser) {
            $query->with(['registrations' => function ($q) use ($currentUser) {
                $q->where('user_id', $currentUser->id)
                  ->select('id', 'event_id', 'user_id', 'status');
            }]);
        }

        // Apply filters
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('format')) {
            $query->where('format', $request->format);
        }

        if ($request->filled('date_range')) {
            match ($request->date_range) {
                'today' => $query->whereDate('start_date', today()),
                'this_week' => $query->whereBetween('start_date', [now()->startOfWeek(), now()->endOfWeek()]),
                'this_month' => $query->whereMonth('start_date', now()->month)->whereYear('start_date', now()->year),
                'next_month' => $query->whereMonth('start_date', now()->addMonth()->month)->whereYear('start_date', now()->addMonth()->year),
                default => null,
            };
        }

        if ($request->filled('specific_date')) {
            $query->whereDate('start_date', $request->specific_date);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                    ->orWhere('description', 'like', "%{$request->search}%")
                    ->orWhere('location', 'like', "%{$request->search}%");
            });
        }

        $events = $query->paginate($request->get('per_page', 12));

        return $this->paginatedResponse($events);
    }

    /**
     * Get event categories.
     */
    public function categories(Request $request): JsonResponse
    {
        $categories = \App\Models\EventCategory::where('is_active', true)
            ->orderBy('name')
            ->get();

        return $this->successResponse(
            \App\Http\Resources\CategoryResource::collection($categories)
        );
    }

    /**
     * Store a newly created event.
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Event::class);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date|after:now',
            'end_date' => 'nullable|date|after:start_date',
            'location' => 'required|string|max:255',
            'max_attendees' => 'nullable|integer|min:1',
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'registration_deadline' => 'required|date|after:now|before:start_date',
            'waitlist_enabled' => 'boolean',
            'format' => 'nullable|in:online,in-person,hybrid',
            'category_id' => 'required|exists:event_categories,id',
            'agenda' => 'nullable|array',
        ]);

        $validated['created_by'] = $request->user()->id;
        $validated['is_active'] = true;
        $validated['waitlist_enabled'] = $request->has('waitlist_enabled') ? true : false;

        // Handle banner image upload
        if ($request->hasFile('banner_image')) {
            $validated['banner_image'] = $request->file('banner_image')
                ->store('events/banners', 'public');
        }

        $event = Event::create($validated);
        $event->load(['creator', 'category']);

        return $this->successResponse(new EventResource($event), 'Event created successfully', 201);
    }

    /**
     * Display the specified event.
     */
    public function show(Request $request, Event $event): JsonResponse
    {
        if (! $event->is_active && ! $this->canViewInactive($event)) {
            return $this->notFoundResponse();
        }

        $user = $this->resolveUser($request);
        
        // Debug logging
        \Log::info("EventController@show for event {$event->id}: user=" . ($user ? "ID:{$user->id}" : "NOT_AUTHENTICATED"));
        
        // Load all relationships in one call
        $relationships = ['creator', 'category'];
        
        // Add user's registration if authenticated
        if ($user) {
            \Log::info("EventController@show: Adding registrations relationship for user {$user->id}");
            $relationships['registrations'] = function ($query) use ($user) {
                \Log::info("EventController@show: Registration query closure executing for user {$user->id}");
                $query->where('user_id', $user->id);
            };
        } else {
            \Log::warning("EventController@show: User not authenticated, skipping registrations");
        }
        
        $event->load($relationships);
        
        // Verify registration was loaded
        if ($user) {
            $loadedCount = $event->registrations->count();
            \Log::info("EventController@show: After load, registrations count = {$loadedCount}");
            if ($loadedCount > 0) {
                \Log::info("EventController@show: Registration found - status: {$event->registrations->first()->status->value}");
            }
        }

        return $this->successResponse(new EventResource($event));
    }

    /**
     * Attempt to resolve the authenticated user for public routes.
     * Tries Request->user(), then falls back to Sanctum PersonalAccessToken parsing.
     */
    private function resolveUser(Request $request): ?\Illuminate\Contracts\Auth\Authenticatable
    {
        if ($request->user()) {
            return $request->user();
        }

        $authHeader = $request->header('Authorization');
        if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
            $token = substr($authHeader, 7);
            if (! empty($token)) {
                $accessToken = PersonalAccessToken::findToken($token);
                if ($accessToken) {
                    return $accessToken->tokenable;
                }
            }
        }

        return null;
    }

    /**
     * Update the specified event.
     */
    public function update(Request $request, Event $event): JsonResponse
    {
        $this->authorize('update', $event);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'nullable|date|after:start_date',
            'location' => 'sometimes|required|string|max:255',
            'max_attendees' => 'nullable|integer|min:1',
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'registration_deadline' => 'sometimes|required|date|before:start_date',
            'waitlist_enabled' => 'boolean',
            'format' => 'nullable|in:online,in-person,hybrid',
            'category_id' => 'sometimes|required|exists:event_categories,id',
            'agenda' => 'nullable|array',
        ]);

        // Handle banner image upload
        if ($request->hasFile('banner_image')) {
            if ($event->banner_image) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($event->banner_image);
            }

            $validated['banner_image'] = $request->file('banner_image')
                ->store('events/banners', 'public');
        }

        $validated['waitlist_enabled'] = $request->has('waitlist_enabled') ? true : false;

        $event->update($validated);
        $event->load(['creator', 'category']);

        return $this->successResponse(new EventResource($event), 'Event updated successfully');
    }

    /**
     * Remove the specified event.
     */
    public function destroy(Request $request, Event $event): JsonResponse
    {
        $this->authorize('delete', $event);

        // Delete banner image
        if ($event->banner_image) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($event->banner_image);
        }

        $event->delete();

        return $this->successResponse(null, 'Event deleted successfully', 204);
    }

    /**
     * Register for an event.
     */
    public function register(Request $request, Event $event): JsonResponse
    {
        $user = $request->user();
        
        try {
            // Use database transaction to prevent race conditions
            $registration = \Illuminate\Support\Facades\DB::transaction(function () use ($event, $user) {
                // Check if event is active
                if (! $event->is_active) {
                    throw new \Exception('This event is not active');
                }
                
                // Check if registration deadline has passed
                if ($event->registration_deadline && $event->registration_deadline->isPast()) {
                    throw new \Exception('Registration deadline has passed');
                }
                
                // Check if event has started
                if ($event->start_date->isPast()) {
                    throw new \Exception('This event has already started');
                }
                
                // Double-check if user is already registered (with lock)
                $existingRegistration = $event->registrations()
                    ->where('user_id', $user->id)
                    ->lockForUpdate()
                    ->first();
                    
                if ($existingRegistration) {
                    throw new \Exception('You are already registered for this event');
                }
                
                // Check if event is full
                if ($event->isFull() && ! $event->waitlist_enabled) {
                    throw new \Exception('This event is full');
                }
                
                if (! $event->canUserRegister($user)) {
                    throw new \Exception('You cannot register for this event');
                }

                return $event->registerUser($user);
            });

            $message = $registration->status === \App\EventRegistrationStatus::CONFIRMED
                ? 'Successfully registered for the event!'
                : 'Added to waitlist. You will be notified if a spot becomes available.';

            return $this->successResponse([
                'registration' => [
                    'id' => $registration->id,
                    'status' => $registration->status->value,
                    'ticket_code' => $registration->ticket_code,
                ],
            ], $message, 201);
            
        } catch (\Illuminate\Database\QueryException $e) {
            // Catch unique constraint violation (duplicate registration)
            if ($e->getCode() === '23000') {
                return $this->errorResponse('You are already registered for this event', null, 400);
            }
            throw $e;
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 400);
        }
    }

    /**
     * Cancel event registration.
     */
    public function cancelRegistration(Request $request, Event $event): JsonResponse
    {
        $registration = $event->registrations()
            ->where('user_id', $request->user()->id)
            ->first();

        if (! $registration) {
            return $this->errorResponse('You are not registered for this event', null, 404);
        }

        $wasConfirmed = $registration->status === \App\EventRegistrationStatus::CONFIRMED;
        $registration->delete();

        // Promote from waitlist if needed
        if ($wasConfirmed && $event->waitlist_enabled) {
            $event->promoteFromWaitlist(1);
        }

        return $this->successResponse(null, 'Registration cancelled successfully', 204);
    }

    /**
     * Check in to an event.
     */
    public function checkIn(Request $request, Event $event): JsonResponse
    {
        $validated = $request->validate([
            'ticket_code' => 'required|string',
        ]);

        $registration = $event->registrations()
            ->where('ticket_code', $validated['ticket_code'])
            ->where('status', \App\EventRegistrationStatus::CONFIRMED->value)
            ->first();

        if (! $registration) {
            return $this->errorResponse('Invalid ticket code', null, 400);
        }

        if ($registration->checked_in) {
            return $this->errorResponse('Already checked in', null, 400);
        }

        $registration->checkIn();

        return $this->successResponse([
            'user' => $registration->user->name,
            'checked_in_at' => $registration->checked_in_at?->toISOString(),
        ], 'Check-in successful');
    }

    /**
     * Show event registrations (for event creator/admin).
     */
    public function registrations(Request $request, Event $event): JsonResponse
    {
        $this->authorize('view-registrations', $event);

        $registrations = $event->registrations()
            ->with('user')
            ->latest()
            ->paginate($request->get('per_page', 50));

        return $this->paginatedResponse($registrations);
    }

    /**
     * Check if user can view inactive event.
     */
    private function canViewInactive(Event $event): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        // Creator, admin, or superadmin can view inactive events
        return $event->created_by === $user->id
            || $user->hasAnyRole(['admin', 'superadmin']);
    }
}
