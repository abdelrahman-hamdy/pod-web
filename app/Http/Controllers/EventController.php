<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Event::with(['creator', 'category'])
            ->active()
            ->latest();

        // Apply filters
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('format')) {
            $query->where('format', $request->format);
        }

        if ($request->filled('date_range')) {
            switch ($request->date_range) {
                case 'today':
                    $query->whereDate('start_date', today());
                    break;
                case 'this_week':
                    $query->whereBetween('start_date', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'this_month':
                    $query->whereMonth('start_date', now()->month)
                        ->whereYear('start_date', now()->year);
                    break;
                case 'next_month':
                    $query->whereMonth('start_date', now()->addMonth()->month)
                        ->whereYear('start_date', now()->addMonth()->year);
                    break;
            }
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

        $events = $query->paginate(12);

        $categories = \App\Models\EventCategory::getCachedActive();

        // Get user's upcoming event registrations for the widget
        $userRegistrations = collect();
        if (Auth::check()) {
            $userRegistrations = \App\Models\EventRegistration::with(['event:id,title,start_date,location'])
                ->where('user_id', Auth::id())
                ->where('status', '!=', \App\EventRegistrationStatus::CANCELLED->value)
                ->whereHas('event', function ($q) {
                    $q->where('start_date', '>', now())
                        ->where('is_active', true);
                })
                ->latest('created_at')
                ->limit(5)
                ->get();
        }

        return view('events.index', compact('events', 'categories', 'userRegistrations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Event::class);

        $categories = \App\Models\EventCategory::getCachedActive();
        $isEdit = false;
        $event = null;

        return view('events.form', compact('categories', 'isEdit', 'event'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Event::class);

        try {
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
                'agenda_items' => 'nullable|array',
                'agenda_items.*.title' => 'required_with:agenda_items|string|max:255',
                'agenda_items.*.description' => 'nullable|string',
                'agenda_items.*.start_time' => 'required_with:agenda_items|date',
            ]);

            $validated['created_by'] = Auth::id();
            $validated['is_active'] = true;

            // Handle checkbox - if not checked, set to false
            $validated['waitlist_enabled'] = $request->has('waitlist_enabled') ? true : false;

            // Handle banner image upload
            if ($request->hasFile('banner_image')) {
                $validated['banner_image'] = $request->file('banner_image')
                    ->store('events/banners', 'public');
            }

            $event = Event::create($validated);

            // Handle agenda items
            if ($request->has('agenda_items')) {
                foreach ($request->agenda_items as $index => $agendaItem) {
                    if (! empty($agendaItem['title']) && ! empty($agendaItem['start_time'])) {
                        $event->agendaItems()->create([
                            'title' => $agendaItem['title'],
                            'start_time' => $agendaItem['start_time'],
                            'order' => $index,
                        ]);
                    }
                }
            }

            return redirect()->route('events.show', $event)
                ->with('success', 'Event created successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'An error occurred while creating the event: '.$e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        $event->load(['creator', 'confirmedRegistrations.user', 'waitlistedRegistrations', 'agendaItems', 'category']);

        $userRegistration = null;
        if (Auth::check()) {
            $userRegistration = $event->registrations()
                ->where('user_id', Auth::id())
                ->first();
        }

        $availableSpots = $event->getAvailableSpots();
        $isRegistrationOpen = $event->isRegistrationOpen();
        $eventStatus = $event->getStatus();

        return view('events.show', compact(
            'event',
            'userRegistration',
            'availableSpots',
            'isRegistrationOpen',
            'eventStatus'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Event $event)
    {
        $this->authorize('update', $event);

        $event->load('agendaItems');
        $categories = \App\Models\EventCategory::getCachedActive();
        $isEdit = true;

        return view('events.form', compact('event', 'categories', 'isEdit'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event)
    {
        $this->authorize('update', $event);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'location' => 'required|string|max:255',
            'max_attendees' => 'nullable|integer|min:1',
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'registration_deadline' => 'required|date|before:start_date',
            'waitlist_enabled' => 'boolean',
            'format' => 'nullable|in:online,in-person,hybrid',
            'category_id' => 'required|exists:event_categories,id',
            'agenda_items' => 'nullable|array',
            'agenda_items.*.title' => 'required_with:agenda_items|string|max:255',
            'agenda_items.*.start_time' => 'required_with:agenda_items|date',
        ]);

        // Handle banner image upload
        if ($request->hasFile('banner_image')) {
            // Delete old banner image
            if ($event->banner_image) {
                Storage::disk('public')->delete($event->banner_image);
            }

            $validated['banner_image'] = $request->file('banner_image')
                ->store('events/banners', 'public');
        }

        // Handle checkbox - if not checked, set to false
        $validated['waitlist_enabled'] = $request->has('waitlist_enabled') ? true : false;

        $event->update($validated);

        // Handle agenda items
        if ($request->has('agenda_items')) {
            // Delete existing agenda items
            $event->agendaItems()->delete();

            // Create new agenda items
            foreach ($request->agenda_items as $index => $agendaItem) {
                if (! empty($agendaItem['title']) && ! empty($agendaItem['start_time'])) {
                    $event->agendaItems()->create([
                        'title' => $agendaItem['title'],
                        'start_time' => $agendaItem['start_time'],
                        'order' => $index,
                    ]);
                }
            }
        }

        return redirect()->route('events.show', $event)
            ->with('success', 'Event updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        $this->authorize('delete', $event);

        // Delete banner image
        if ($event->banner_image) {
            Storage::disk('public')->delete($event->banner_image);
        }

        $event->delete();

        return redirect()->route('events.index')
            ->with('success', 'Event deleted successfully!');
    }

    /**
     * Register for an event.
     */
    public function register(Event $event)
    {
        if (! $event->canUserRegister(Auth::user())) {
            return redirect()->back()
                ->with('error', 'You cannot register for this event.');
        }

        $registration = $event->registerUser(Auth::user());

        $message = $registration->status === \App\EventRegistrationStatus::CONFIRMED
            ? 'Successfully registered for the event!'
            : 'Added to waitlist. You will be notified if a spot becomes available.';

        return redirect()->route('events.show', $event)
            ->with('success', $message);
    }

    /**
     * Cancel event registration.
     */
    public function cancelRegistration(Event $event)
    {
        $registration = $event->registrations()
            ->where('user_id', Auth::id())
            ->first();

        if (! $registration) {
            return redirect()->back()
                ->with('error', 'You are not registered for this event.');
        }

        $wasConfirmed = $registration->status === \App\EventRegistrationStatus::CONFIRMED;
        $registration->delete();

        // If this was a confirmed registration, promote someone from waitlist
        if ($wasConfirmed && $event->waitlist_enabled) {
            $promoted = $event->promoteFromWaitlist(1);
            // You could send notifications to promoted users here
        }

        return redirect()->route('events.show', $event)
            ->with('success', 'Registration cancelled successfully.');
    }

    /**
     * Check in to an event.
     */
    public function checkIn(Request $request, Event $event)
    {
        $validated = $request->validate([
            'ticket_code' => 'required|string',
        ]);

        $registration = $event->registrations()
            ->where('ticket_code', $validated['ticket_code'])
            ->where('status', \App\EventRegistrationStatus::CONFIRMED->value)
            ->first();

        if (! $registration) {
            return response()->json(['error' => 'Invalid ticket code'], 400);
        }

        if ($registration->checked_in) {
            return response()->json(['error' => 'Already checked in'], 400);
        }

        $registration->update([
            'checked_in' => true,
            'checked_in_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Check-in successful',
            'user' => $registration->user->name,
        ]);
    }

    /**
     * Show event registrations (for event creator).
     */
    public function registrations(Event $event)
    {
        $this->authorize('view-registrations', $event);

        $registrations = $event->registrations()
            ->with('user')
            ->latest()
            ->paginate(50);

        return view('events.registrations', compact('event', 'registrations'));
    }

    /**
     * Export event registrations.
     */
    public function exportRegistrations(Event $event)
    {
        $this->authorize('view-registrations', $event);

        // This would typically export to CSV or Excel
        // For now, just return JSON
        $registrations = $event->registrations()
            ->with('user')
            ->get()
            ->map(function ($registration) {
                return [
                    'name' => $registration->user->name,
                    'email' => $registration->user->email,
                    'status' => $registration->status,
                    'ticket_code' => $registration->ticket_code,
                    'checked_in' => $registration->checked_in ? 'Yes' : 'No',
                    'registered_at' => $registration->created_at->format('Y-m-d H:i:s'),
                ];
            });

        return response()->json($registrations);
    }
}
