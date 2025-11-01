<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate slug when creating
        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });

        // Auto-generate slug when updating
        static::updating(function ($category) {
            if ($category->isDirty('name') && empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });

        // Clear cache when categories are modified
        static::created(function () {
            cache()->forget('categories_active');
            cache()->forget('categories_active_ordered');
        });

        static::updated(function () {
            cache()->forget('categories_active');
            cache()->forget('categories_active_ordered');
        });

        static::deleted(function () {
            cache()->forget('categories_active');
            cache()->forget('categories_active_ordered');
        });
    }

    /**
     * Get all job listings in this category.
     */
    public function jobListings()
    {
        return $this->hasMany(JobListing::class);
    }

    /**
     * Get active job listings in this category.
     */
    public function activeJobListings()
    {
        return $this->hasMany(JobListing::class)->where('status', 'active');
    }

    /**
     * Get the count of active job listings.
     */
    public function getActiveJobsCountAttribute(): int
    {
        return $this->activeJobListings()->count();
    }

    /**
     * Scope for active categories.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordered categories.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Get route key name for model binding.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Get the URL for this category.
     */
    public function getUrlAttribute(): string
    {
        return route('jobs.category', $this->slug);
    }

    /**
     * Get all active categories (cached).
     */
    public static function getCachedActive(): \Illuminate\Support\Collection
    {
        return cache()->remember('categories_active', 3600, function () {
            return static::where('is_active', true)
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Get all active ordered categories (cached).
     */
    public static function getCachedActiveOrdered(): \Illuminate\Support\Collection
    {
        return cache()->remember('categories_active_ordered', 3600, function () {
            return static::where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Get default categories for seeding.
     */
    public static function getDefaultCategories(): array
    {
        return [
            [
                'name' => 'Data Scientist',
                'description' => 'Roles focused on data analysis, machine learning, and statistical modeling',
                'color' => '#4f46e5',
                'sort_order' => 1,
            ],
            [
                'name' => 'ML Engineer',
                'description' => 'Machine learning engineering and MLOps positions',
                'color' => '#7c3aed',
                'sort_order' => 2,
            ],
            [
                'name' => 'Data Analyst',
                'description' => 'Business intelligence and data analysis roles',
                'color' => '#3b82f6',
                'sort_order' => 3,
            ],
            [
                'name' => 'AI Engineer',
                'description' => 'Artificial intelligence development and research positions',
                'color' => '#8b5cf6',
                'sort_order' => 4,
            ],
            [
                'name' => 'Data Engineer',
                'description' => 'Data pipeline, ETL, and infrastructure roles',
                'color' => '#06b6d4',
                'sort_order' => 5,
            ],
            [
                'name' => 'BI Developer',
                'description' => 'Business intelligence and reporting development',
                'color' => '#10b981',
                'sort_order' => 6,
            ],
            [
                'name' => 'Research Scientist',
                'description' => 'AI/ML research and academic positions',
                'color' => '#f59e0b',
                'sort_order' => 7,
            ],
            [
                'name' => 'Product Manager - Data',
                'description' => 'Product management roles focused on data products',
                'color' => '#ef4444',
                'sort_order' => 8,
            ],
        ];
    }
}
