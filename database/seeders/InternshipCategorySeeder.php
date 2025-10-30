<?php

namespace Database\Seeders;

use App\Models\InternshipCategory;
use Illuminate\Database\Seeder;

class InternshipCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Data Science', 'slug' => 'data-science', 'description' => 'Data analysis and machine learning internships'],
            ['name' => 'Software Engineering', 'slug' => 'software-engineering', 'description' => 'Development and programming internships'],
            ['name' => 'Business Intelligence', 'slug' => 'business-intelligence', 'description' => 'BI and analytics internships'],
            ['name' => 'Research', 'slug' => 'research', 'description' => 'Research and academic internships'],
            ['name' => 'Digital Marketing', 'slug' => 'digital-marketing', 'description' => 'Marketing and social media internships'],
        ];

        foreach ($categories as $category) {
            InternshipCategory::create($category);
        }
    }
}
