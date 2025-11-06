<?php

namespace App\Models;

use App\HackathonFormat;
use App\SkillLevel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hackathon extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'banner_image',
        'category_id',
        'start_date',
        'end_date',
        'registration_deadline',
        'max_participants',
        'max_team_size',
        'min_team_size',
        'entry_fee',
        'prize_pool',
        'location',
        'format',
        'skill_requirements',
        'technologies',
        'rules',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'registration_deadline' => 'datetime',
            'skill_requirements' => SkillLevel::class,
            'technologies' => 'array',
            'sponsors' => 'array',
            'prizes' => 'array',
            'is_active' => 'boolean',
            'entry_fee' => 'decimal:2',
            'prize_pool' => 'decimal:2',
            'format' => HackathonFormat::class,
        ];
    }

    protected $attributes = [
        'entry_fee' => 0,
        'is_active' => true,
    ];

    /**
     * Set the entry fee attribute (ensure it's never null).
     */
    public function setEntryFeeAttribute($value): void
    {
        $this->attributes['entry_fee'] = $value ?? 0;
    }

    /**
     * Get the user who created this hackathon.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the category of this hackathon.
     */
    public function category()
    {
        return $this->belongsTo(HackathonCategory::class);
    }

    /**
     * Get all teams for this hackathon.
     */
    public function teams()
    {
        return $this->hasMany(HackathonTeam::class);
    }

    /**
     * Get all participants (users in teams).
     */
    public function participants()
    {
        return $this->hasManyThrough(User::class, HackathonTeam::class, 'hackathon_id', 'id', 'id', 'leader_id')
            ->orWhereHas('teamMembers', function ($query) {
                $query->where('hackathon_id', $this->id);
            });
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
     * Check if hackathon is full.
     */
    public function isFull(): bool
    {
        if (! $this->max_participants) {
            return false;
        }

        return $this->getParticipantCount() >= $this->max_participants;
    }

    /**
     * Get participant count.
     */
    public function getParticipantCount(): int
    {
        return $this->teams()
            ->withCount('members')
            ->get()
            ->sum('members_count') + $this->teams()->count(); // Add team leaders
    }

    /**
     * Get available spots.
     */
    public function getAvailableSpots(): ?int
    {
        if (! $this->max_participants) {
            return null;
        }

        return max(0, $this->max_participants - $this->getParticipantCount());
    }

    /**
     * Check if user can participate.
     */
    public function canUserParticipate($user): bool
    {
        if (! $this->isRegistrationOpen()) {
            return false;
        }

        // Check if user is already in a team
        if ($this->teams()->where('leader_id', $user->id)->exists()) {
            return false;
        }

        if ($this->teams()->whereHas('members', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->exists()) {
            return false;
        }

        return true;
    }

    /**
     * Check if hackathon has started.
     */
    public function hasStarted(): bool
    {
        return $this->start_date <= now();
    }

    /**
     * Check if hackathon has ended.
     */
    public function hasEnded(): bool
    {
        return $this->end_date <= now();
    }

    /**
     * Get hackathon status.
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
     * Get total prize pool formatted.
     */
    public function getFormattedPrizePoolAttribute(): ?string
    {
        if (! $this->prize_pool) {
            return null;
        }

        return 'EGP '.number_format($this->prize_pool, 0);
    }

    /**
     * Get formatted entry fee.
     */
    public function getFormattedEntryFeeAttribute(): ?string
    {
        if (! $this->entry_fee) {
            return 'Free';
        }

        return '$'.number_format($this->entry_fee, 2);
    }

    /**
     * Scope for active hackathons.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for upcoming hackathons.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>', now());
    }

    /**
     * Scope for ongoing hackathons.
     */
    public function scopeOngoing($query)
    {
        return $query->where('start_date', '<=', now())
            ->where('end_date', '>', now());
    }

    /**
     * Scope for past hackathons.
     */
    public function scopePast($query)
    {
        return $query->where('end_date', '<', now());
    }

    /**
     * Scope for hackathons accepting registrations.
     */
    public function scopeAcceptingRegistrations($query)
    {
        return $query->where('is_active', true)
            ->where('registration_deadline', '>', now())
            ->where('start_date', '>', now());
    }

    /**
     * Search hackathons.
     */
    public function scopeSearch($query, $keyword)
    {
        return $query->where(function ($q) use ($keyword) {
            $q->where('title', 'like', "%{$keyword}%")
                ->orWhere('description', 'like', "%{$keyword}%")
                ->orWhereJsonContains('technologies', $keyword);
        });
    }
}
