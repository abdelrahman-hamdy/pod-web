<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'start_date',
        'end_date',
        'location',
        'max_attendees',
        'agenda',
        'banner_image',
        'registration_deadline',
        'chat_opens_at',
        'waitlist_enabled',
        'is_active',
        'created_by',
        'format',
        'category_id',
        'type',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'registration_deadline' => 'datetime',
        'chat_opens_at' => 'datetime',
        'waitlist_enabled' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user who created this event.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the category for this event.
     */
    public function category()
    {
        return $this->belongsTo(EventCategory::class);
    }

    /**
     * Get all registrations for this event.
     */
    public function registrations()
    {
        return $this->hasMany(EventRegistration::class);
    }

    /**
     * Get confirmed registrations only.
     */
    public function confirmedRegistrations()
    {
        return $this->hasMany(EventRegistration::class)->where('status', \App\EventRegistrationStatus::CONFIRMED->value);
    }

    /**
     * Get waitlisted registrations only.
     */
    public function waitlistedRegistrations()
    {
        return $this->hasMany(EventRegistration::class)->where('status', \App\EventRegistrationStatus::WAITLISTED->value);
    }

    /**
     * Get all attendees (confirmed registrations).
     */
    public function attendees()
    {
        return $this->belongsToMany(User::class, 'event_registrations')
            ->wherePivot('status', \App\EventRegistrationStatus::CONFIRMED->value)
            ->withPivot(['ticket_code', 'text_code', 'checked_in', 'checked_in_at'])
            ->withTimestamps();
    }

    /**
     * Check if registration is open.
     */
    public function isRegistrationOpen(): bool
    {
        return $this->is_active
            && $this->registration_deadline > now()
            && $this->start_date > now();
    }

    /**
     * Check if event is full (reached max attendees).
     */
    public function isFull(): bool
    {
        if (! $this->max_attendees) {
            return false; // Unlimited capacity
        }

        return $this->confirmedRegistrations()->count() >= $this->max_attendees;
    }

    /**
     * Get available spots remaining.
     */
    public function getAvailableSpots(): ?int
    {
        if (! $this->max_attendees) {
            return null; // Unlimited
        }

        return max(0, $this->max_attendees - $this->confirmedRegistrations()->count());
    }

    /**
     * Check if user can register for this event.
     */
    public function canUserRegister($user): bool
    {
        if (! $this->isRegistrationOpen()) {
            return false;
        }

        // Check if user already registered
        if ($this->registrations()->where('user_id', $user->id)->exists()) {
            return false;
        }

        // Check if event is full and waitlist is disabled
        if ($this->isFull() && ! $this->waitlist_enabled) {
            return false;
        }

        return true;
    }

    /**
     * Register a user for this event.
     */
    public function registerUser($user): EventRegistration
    {
        if (! $this->canUserRegister($user)) {
            throw new \Exception('User cannot register for this event.');
        }

        $status = $this->isFull() && $this->waitlist_enabled
            ? \App\EventRegistrationStatus::WAITLISTED
            : \App\EventRegistrationStatus::CONFIRMED;

        $registration = $this->registrations()->create([
            'user_id' => $user->id,
            'status' => $status,
            'ticket_code' => $this->generateTicketCode(),
            'text_code' => $this->generateTextCode(),
        ]);

        return $registration;
    }

    /**
     * Generate unique ticket code (for QR code).
     */
    private function generateTicketCode(): string
    {
        do {
            $code = 'EVT-'.$this->id.'-'.strtoupper(substr(md5(uniqid()), 0, 8));
        } while (EventRegistration::where('ticket_code', $code)->exists());

        return $code;
    }

    /**
     * Generate unique text verification code.
     */
    private function generateTextCode(): string
    {
        do {
            $code = strtoupper(substr(md5(uniqid()), 0, 6));
        } while (EventRegistration::where('text_code', $code)->exists());

        return $code;
    }

    /**
     * Promote users from waitlist to confirmed.
     */
    public function promoteFromWaitlist($count = 1): array
    {
        $promoted = [];

        if ($this->isFull()) {
            return $promoted;
        }

        $availableSpots = $this->getAvailableSpots() ?? $count;
        $toPromote = min($count, $availableSpots);

        $waitlisted = $this->waitlistedRegistrations()
            ->orderBy('created_at')
            ->limit($toPromote)
            ->get();

        foreach ($waitlisted as $registration) {
            $registration->update(['status' => \App\EventRegistrationStatus::CONFIRMED]);
            $promoted[] = $registration;
        }

        return $promoted;
    }

    /**
     * Check if event chat is available.
     */
    public function isChatAvailable(): bool
    {
        return $this->chat_opens_at && $this->chat_opens_at <= now();
    }

    /**
     * Check if event has started.
     */
    public function hasStarted(): bool
    {
        return $this->start_date <= now();
    }

    /**
     * Check if event has ended.
     */
    public function hasEnded(): bool
    {
        $endTime = $this->end_date ?: $this->start_date->addHours(2); // Default 2 hours

        return $endTime <= now();
    }

    /**
     * Get event status.
     */
    public function getStatus(): string
    {
        if (! $this->is_active) {
            return 'inactive';
        }

        if ($this->hasEnded()) {
            return 'ended';
        }

        if ($this->hasStarted()) {
            return 'ongoing';
        }

        if (! $this->isRegistrationOpen()) {
            return 'registration_closed';
        }

        return 'upcoming';
    }

    /**
     * Scope for active events.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for upcoming events.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>', now());
    }

    /**
     * Scope for past events.
     */
    public function scopePast($query)
    {
        return $query->where('start_date', '<', now());
    }

    /**
     * Get all agenda items for this event.
     */
    public function agendaItems()
    {
        return $this->hasMany(\App\Models\AgendaItem::class)->orderBy('order');
    }
}
