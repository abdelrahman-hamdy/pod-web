<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InternshipApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'internship_id',
        'full_name',
        'email',
        'phone',
        'university',
        'major',
        'graduation_status',
        'experience',
        'interest_categories',
        'availability_start',
        'availability_end',
        'motivation',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'interest_categories' => 'array',
            'availability_start' => 'date',
            'availability_end' => 'date',
        ];
    }

    public function graduationStatus(): ?\App\GraduationStatus
    {
        return $this->graduation_status ? \App\GraduationStatus::from($this->graduation_status) : null;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function internship(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Internship::class);
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'yellow',
            'under_review' => 'blue',
            'accepted' => 'green',
            'rejected' => 'red',
            'withdrawn' => 'gray',
            default => 'gray',
        };
    }

    public function getStatusDisplayAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Pending Review',
            'under_review' => 'Under Review',
            'accepted' => 'Accepted',
            'rejected' => 'Rejected',
            'withdrawn' => 'Withdrawn',
            default => 'Unknown',
        };
    }
}
