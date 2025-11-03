<?php

namespace Database\Seeders;

use App\Models\EventCategory;
use Illuminate\Database\Seeder;

class EventCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Technology',
                'color' => '#3B82F6',
            ],
            [
                'name' => 'Business',
                'color' => '#10B981',
            ],
            [
                'name' => 'Education',
                'color' => '#F59E0B',
            ],
            [
                'name' => 'Healthcare',
                'color' => '#EF4444',
            ],
            [
                'name' => 'Creative',
                'color' => '#8B5CF6',
            ],
            [
                'name' => 'Sports',
                'color' => '#06B6D4',
            ],
        ];

        foreach ($categories as $category) {
            EventCategory::create($category);
        }
    }
}
