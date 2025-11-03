<?php

namespace App\Models;

use App\ExperienceLevel;
use App\Gender;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The data type of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'int';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'name',
        'email',
        'password',
        'phone',
        'city',
        'country',
        'gender',
        'bio',
        'title',
        'company',
        'avatar',
        'avatar_color',
        'birthday',
        'skills',
        'experience_level',
        'education',
        'portfolio_links',
        'linkedin_url',
        'github_url',
        'twitter_url',
        'website_url',
        'google_id',
        'linkedin_id',
        'provider',
        'provider_id',
        'profile_completed',
        'is_active',
        'role',
        'active_status',
        'fcm_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'birthday',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birthday' => 'date',
            'gender' => Gender::class,
            'experience_level' => ExperienceLevel::class,
            'skills' => 'array',
            'education' => 'array',
            'portfolio_links' => 'array',
            'profile_completed' => 'boolean',
            'is_active' => 'boolean',
            'active_status' => 'boolean',
        ];
    }

    /**
     * Get the user's full name.
     */
    public function getNameAttribute(): string
    {
        return $this->first_name.' '.$this->last_name;
    }

    /**
     * Get the user's avatar URL using centralized helper.
     */
    public function getAvatarUrlAttribute(): string
    {
        return user_avatar($this);
    }

    /**
     * Check if profile is complete.
     */
    public function isProfileComplete(): bool
    {
        $requiredFields = [
            'first_name', 'last_name', 'email', 'phone', 'city',
            'country', 'gender', 'bio', 'skills', 'experience_level',
        ];

        foreach ($requiredFields as $field) {
            if (empty($this->{$field})) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get profile completion percentage.
     */
    public function getProfileCompletionPercentage(): int
    {
        $allFields = [
            'first_name', 'last_name', 'email', 'phone', 'city',
            'country', 'gender', 'bio', 'avatar', 'skills',
            'experience_level', 'education', 'linkedin_url', 'github_url',
        ];

        $completedFields = 0;
        foreach ($allFields as $field) {
            if (! empty($this->{$field})) {
                $completedFields++;
            }
        }

        return round(($completedFields / count($allFields)) * 100);
    }

    /**
     * Get the user's posts.
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Get the user's experiences.
     */
    public function experiences(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Experience::class)->orderBy('start_date', 'desc');
    }

    /**
     * Get the user's portfolios.
     */
    public function portfolios(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Portfolio::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get the user's job listings.
     */
    public function jobListings()
    {
        return $this->hasMany(JobListing::class, 'posted_by');
    }

    /**
     * Get the user's job applications.
     */
    public function jobApplications()
    {
        return $this->hasMany(JobApplication::class);
    }

    /**
     * Get the user's event registrations.
     */
    public function eventRegistrations()
    {
        return $this->hasMany(EventRegistration::class);
    }

    /**
     * Get hackathons created by the user.
     */
    public function hackathons()
    {
        return $this->hasMany(Hackathon::class, 'created_by');
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if user has any of the specified roles.
     */
    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles);
    }

    /**
     * Check if user is a super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'superadmin';
    }

    /**
     * Check if user is an admin (super admin or admin).
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, ['superadmin', 'admin']);
    }

    /**
     * Check if user is a client.
     */
    public function isClient(): bool
    {
        return $this->role === 'client';
    }

    /**
     * Check if user is a regular user.
     */
    public function isRegularUser(): bool
    {
        return $this->role === 'user';
    }

    /**
     * Check if user can create events.
     */
    public function canCreateEvents(): bool
    {
        return $this->isAdmin() || $this->isClient();
    }

    /**
     * Check if user can post jobs.
     */
    public function canPostJobs(): bool
    {
        return $this->isAdmin() || $this->isClient();
    }

    /**
     * Check if user can create hackathons.
     */
    public function canCreateHackathons(): bool
    {
        return $this->isAdmin() || $this->isClient();
    }

    /**
     * Check if user can create internships.
     */
    public function canCreateInternships(): bool
    {
        return $this->isAdmin() || $this->isClient();
    }

    /**
     * Check if user can moderate content.
     */
    public function canModerateContent(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Check if user can manage users.
     */
    public function canManageUsers(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Check if user can access admin panel.
     */
    public function canAccessAdmin(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Check if user can access Filament admin panel.
     */
    public function canAccessPanel(\Filament\Panel $panel): bool
    {
        // Only superadmin and admin can access Filament panel
        return $this->isAdmin();
    }

    /**
     * Get the user's client conversion requests.
     */
    public function clientConversionRequests()
    {
        return $this->hasMany(ClientConversionRequest::class);
    }

    /**
     * Get the client conversion requests reviewed by this user.
     */
    public function reviewedClientConversionRequests()
    {
        return $this->hasMany(ClientConversionRequest::class, 'reviewed_by');
    }

    /**
     * Get the user's status enum.
     */
    public function getStatus(): \App\UserStatus
    {
        return $this->active_status ? \App\UserStatus::ONLINE : \App\UserStatus::OFFLINE;
    }

    /**
     * Check if the user is online.
     */
    public function isOnline(): bool
    {
        return $this->active_status;
    }

    /**
     * Set the user's online status.
     */
    public function setOnlineStatus(bool $status): void
    {
        $this->update(['active_status' => $status]);
    }

    /**
     * Get the user's avatar color.
     */
    public function getAvatarColor(): string
    {
        if ($this->avatar_color) {
            return $this->avatar_color;
        }

        // Generate a consistent color based on the user's name using enum
        $colorIndex = crc32($this->name) % count(\App\AvatarColor::cases());
        $color = \App\AvatarColor::byIndex($colorIndex);

        // Save the color to the database for future use
        $this->update(['avatar_color' => $color->value]);

        return $color->value;
    }

    /**
     * Boot method removed - avatar colors are now set explicitly during registration
     * using AvatarColor::random()->value
     */
    public function hackathonTeams()
    {
        return $this->hasMany(HackathonTeam::class);
    }

    public function newsletterSubscription()
    {
        return $this->hasOne(NewsletterSubscription::class);
    }

    /**
     * Get the user's notification preferences.
     */
    public function notificationPreferences()
    {
        return $this->hasOne(UserNotificationPreference::class)->withDefault([
            'email_notifications' => true,
            'push_notifications' => true,
            'in_app_notifications' => true,
        ]);
    }
}
