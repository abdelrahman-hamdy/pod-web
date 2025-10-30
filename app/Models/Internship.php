<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Internship extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'company_name',
        'category_id',
        'location',
        'type',
        'duration',
        'application_deadline',
        'start_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'application_deadline' => 'date',
            'start_date' => 'date',
        ];
    }

    public function category()
    {
        return $this->belongsTo(InternshipCategory::class);
    }

    public function applications()
    {
        return $this->hasMany(\App\Models\InternshipApplication::class);
    }
}
