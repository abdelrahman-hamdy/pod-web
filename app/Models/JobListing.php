<?php

namespace App\Models;

use App\ExperienceLevel;
use App\JobStatus;
use App\LocationType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobListing extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'company_name',
        'company_description',
        'location_type',
        'location',
        'salary_min',
        'salary_max',
        'required_skills',
        'experience_level',
        'application_deadline',
        'category_id',
        'status',
        'posted_by',
    ];

    protected $casts = [
        'required_skills' => 'array',
        'application_deadline' => 'date',
        'location_type' => LocationType::class,
        'experience_level' => ExperienceLevel::class,
        'status' => 'string',
    ];

    /**
     * Get the category this job belongs to.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the user who posted this job.
     */
    public function poster()
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    /**
     * Get all applications for this job.
     */
    public function applications()
    {
        return $this->hasMany(JobApplication::class);
    }

    /**
     * Get the current user's application for this job (for eager loading).
     */
    public function userApplication()
    {
        return $this->hasOne(JobApplication::class)->where('user_id', auth()->id());
    }

    /**
     * Get pending applications only.
     */
    public function pendingApplications()
    {
        return $this->hasMany(JobApplication::class)->where('status', 'pending');
    }

    /**
     * Get reviewed applications only.
     */
    public function reviewedApplications()
    {
        return $this->hasMany(JobApplication::class)->where('status', 'reviewed');
    }

    /**
     * Get accepted applications only.
     */
    public function acceptedApplications()
    {
        return $this->hasMany(JobApplication::class)->where('status', 'accepted');
    }

    /**
     * Get all applicants (users who applied).
     */
    public function applicants()
    {
        return $this->belongsToMany(User::class, 'job_applications')
            ->withPivot(['status', 'cover_letter', 'additional_info', 'admin_notes', 'status_updated_at'])
            ->withTimestamps();
    }

    /**
     * Check if job is active and accepting applications.
     */
    public function isAcceptingApplications(): bool
    {
        return $this->status === JobStatus::ACTIVE->value
            && $this->application_deadline->copy()->startOfDay() >= now()->startOfDay();
    }

    /**
     * Check if application deadline has passed.
     */
    public function hasDeadlinePassed(): bool
    {
        return $this->application_deadline->copy()->startOfDay() < now()->startOfDay();
    }

    /**
     * Check if user can apply for this job.
     */
    public function canUserApply($user): bool
    {
        if (! $this->isAcceptingApplications()) {
            return false;
        }

        // Check if user already applied
        if ($this->applications()->where('user_id', $user->id)->exists()) {
            return false;
        }

        return true;
    }

    /**
     * Get user's application for this job.
     */
    public function getUserApplication($user): ?JobApplication
    {
        return $this->applications()->where('user_id', $user->id)->first();
    }

    /**
     * Apply for this job.
     */
    public function applyForJob($user, array $applicationData): JobApplication
    {
        if (! $this->canUserApply($user)) {
            throw new \Exception('User cannot apply for this job.');
        }

        $application = $this->applications()->create(array_merge($applicationData, [
            'user_id' => $user->id,
            'status' => \App\JobApplicationStatus::PENDING->value,
        ]));

        return $application;
    }

    /**
     * Get formatted salary range.
     */
    public function getFormattedSalaryAttribute(): ?string
    {
        if (! $this->salary_min && ! $this->salary_max) {
            return null;
        }

        $currency = $this->salary_currency ?? 'EGP';

        if ($this->salary_min && $this->salary_max) {
            return "{$currency} {$this->salary_min} - {$this->salary_max}";
        }

        if ($this->salary_min) {
            return "{$currency} {$this->salary_min}+";
        }

        return "{$currency} up to {$this->salary_max}";
    }

    /**
     * Get days until deadline.
     */
    public function getDaysUntilDeadlineAttribute(): int
    {
        return now()->startOfDay()->diffInDays($this->application_deadline, false);
    }

    /**
     * Get job urgency level.
     */
    public function getUrgencyLevelAttribute(): string
    {
        $daysLeft = $this->days_until_deadline;

        if ($daysLeft < 0) {
            return 'expired';
        }

        if ($daysLeft <= 3) {
            return 'urgent';
        }

        if ($daysLeft <= 7) {
            return 'moderate';
        }

        return 'low';
    }

    /**
     * Get total applications count.
     */
    public function getApplicationsCountAttribute(): int
    {
        return $this->applications()->count();
    }

    /**
     * Get job status for display.
     */
    public function getDisplayStatusAttribute(): string
    {
        if ($this->status === JobStatus::ARCHIVED->value) {
            return 'Archived';
        }

        if ($this->status === JobStatus::CLOSED->value) {
            return 'Closed';
        }

        if ($this->hasDeadlinePassed()) {
            return 'Deadline Passed';
        }

        if ($this->status === JobStatus::ACTIVE->value) {
            return 'Open';
        }

        return JobStatus::from($this->status)->getLabel();
    }

    /**
     * Close this job listing.
     */
    public function close(): void
    {
        $this->update(['status' => 'closed']);
    }

    /**
     * Archive this job listing.
     */
    public function archive(): void
    {
        $this->update(['status' => 'archived']);
    }

    /**
     * Reopen this job listing.
     */
    public function reopen(): void
    {
        $this->update(['status' => 'active']);
    }

    /**
     * Scope for active jobs.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for jobs with open applications.
     */
    public function scopeAcceptingApplications($query)
    {
        return $query->where('status', 'active')
            ->where('application_deadline', '>=', now()->startOfDay());
    }

    /**
     * Scope for jobs by category.
     */
    public function scopeInCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope for jobs by category ID.
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope for jobs by experience level.
     */
    public function scopeForExperienceLevel($query, $level)
    {
        return $query->where('experience_level', $level);
    }

    /**
     * Scope for jobs by location type.
     */
    public function scopeByLocationType($query, $type)
    {
        return $query->where('location_type', $type);
    }

    /**
     * Scope for recent jobs.
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope for jobs with specific skills.
     */
    public function scopeWithSkills($query, array $skills)
    {
        return $query->where(function ($q) use ($skills) {
            foreach ($skills as $skill) {
                $q->orWhereJsonContains('required_skills', $skill);
            }
        });
    }

    /**
     * Search jobs by keyword.
     */
    public function scopeSearch($query, $keyword)
    {
        return $query->where(function ($q) use ($keyword) {
            $q->where('title', 'like', "%{$keyword}%")
                ->orWhere('description', 'like', "%{$keyword}%")
                ->orWhere('company_name', 'like', "%{$keyword}%")
                ->orWhereJsonContains('required_skills', $keyword);
        });
    }
}
