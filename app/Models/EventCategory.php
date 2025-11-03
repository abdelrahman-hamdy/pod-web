<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'color',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Clear cache when event categories are modified
        static::created(function () {
            cache()->forget('event_categories_active');
        });

        static::updated(function () {
            cache()->forget('event_categories_active');
        });

        static::deleted(function () {
            cache()->forget('event_categories_active');
        });
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'category_id');
    }

    public function getColorAttribute($value)
    {
        return $value ?: '#3B82F6';
    }

    /**
     * Get all active event categories (cached).
     */
    public static function getCachedActive(): \Illuminate\Support\Collection
    {
        return cache()->remember('event_categories_active', 3600, function () {
            return static::where('is_active', true)
                ->orderBy('name')
                ->get();
        });
    }
}
