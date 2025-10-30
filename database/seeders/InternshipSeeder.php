<?php

namespace Database\Seeders;

use App\Models\Internship;
use App\Models\InternshipCategory;
use Illuminate\Database\Seeder;

class InternshipSeeder extends Seeder
{
    public function run(): void
    {
        $categories = InternshipCategory::all();

        if ($categories->isEmpty()) {
            $this->command->warn('No categories found. Please run InternshipCategorySeeder first.');

            return;
        }

        foreach ($categories as $category) {
            $count = rand(1, 4); // Random count between 1-4 internships per category

            for ($i = 1; $i <= $count; $i++) {
                Internship::create([
                    'title' => $this->getTitle($category->name, $i),
                    'description' => $this->getDescription($category->name),
                    'company_name' => $this->getCompanyName(),
                    'category_id' => $category->id,
                    'location' => $this->getLocation(),
                    'type' => ['full_time', 'part_time', 'remote', 'hybrid'][array_rand(['full_time', 'part_time', 'remote', 'hybrid'])],
                    'duration' => ['3 months', '6 months', '12 months'][array_rand(['3 months', '6 months', '12 months'])],
                    'application_deadline' => now()->addDays(rand(30, 90)),
                    'start_date' => now()->addDays(rand(30, 90)),
                    'status' => 'open',
                ]);
            }
        }
    }

    private function getTitle($category, $index): string
    {
        $titles = [
            'Data Science' => ['Junior Data Science Intern', 'Machine Learning Intern', 'Data Analytics Intern', 'AI Research Intern'],
            'Software Engineering' => ['Frontend Developer Intern', 'Backend Developer Intern', 'Full Stack Intern', 'DevOps Intern'],
            'Business Intelligence' => ['BI Analyst Intern', 'Business Analyst Intern', 'Data Visualization Intern', 'Reporting Analyst Intern'],
            'Research' => ['Research Assistant Intern', 'Lab Intern', 'Academic Research Intern', 'Field Research Intern'],
            'Digital Marketing' => ['Social Media Intern', 'Content Marketing Intern', 'SEO Intern', 'Digital Ads Intern'],
        ];

        return $titles[$category][$index - 1] ?? "{$category} Intern #{$index}";
    }

    private function getDescription($category): string
    {
        $descriptions = [
            'Data Science' => 'Join our data science team and work on cutting-edge machine learning projects. You will assist in data collection, analysis, and model development. Learn from experienced data scientists and contribute to real-world projects.',
            'Software Engineering' => 'Work alongside our engineering team to build scalable web applications. You will write code, participate in code reviews, and learn industry best practices. Great opportunity to gain hands-on experience in modern development.',
            'Business Intelligence' => 'Help transform raw data into actionable insights. You will work with business stakeholders to understand requirements and create reports and dashboards. Experience with SQL and BI tools preferred.',
            'Research' => 'Support ongoing research projects in our lab. You will assist with data collection, literature reviews, and experimental design. Ideal for students interested in academic or applied research careers.',
            'Digital Marketing' => 'Manage our social media presence and content marketing initiatives. You will create content, analyze performance metrics, and develop marketing strategies. Learn digital marketing tools and best practices.',
        ];

        return $descriptions[$category] ?? 'Excellent opportunity to gain practical experience and build your professional network.';
    }

    private function getCompanyName(): string
    {
        $companies = [
            'TechCorp Solutions', 'Data Insights Ltd', 'InnovateAI', 'CloudTech Systems',
            'NextGen Analytics', 'SmartData Solutions', 'FutureTech Inc', 'CodeMaster Labs',
        ];

        return $companies[array_rand($companies)];
    }

    private function getLocation(): string
    {
        $locations = ['Cairo, Egypt', 'Remote', 'Alexandria, Egypt', 'Giza, Egypt'];

        return $locations[array_rand($locations)];
    }
}
