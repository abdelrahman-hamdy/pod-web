<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first user as the event creator
        $user = User::first();

        if (! $user) {
            // Create a default user if none exists
            $user = User::create([
                'name' => 'Event Organizer',
                'email' => 'organizer@example.com',
                'password' => bcrypt('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]);
        }

        $events = [
            [
                'title' => 'AI Summit Egypt 2024',
                'description' => 'Join Egypt\'s largest AI conference featuring international speakers, workshops, and networking opportunities. This comprehensive event will cover the latest trends in artificial intelligence, machine learning, and data science.',
                'start_date' => Carbon::now()->addDays(15)->setTime(9, 0),
                'end_date' => Carbon::now()->addDays(15)->setTime(18, 0),
                'location' => 'Cairo International Convention Center, Cairo, Egypt',
                'max_attendees' => 500,
                'agenda' => '9:00 AM - Registration & Welcome\n10:00 AM - Keynote: The Future of AI\n11:30 AM - Panel: AI in Healthcare\n1:00 PM - Lunch Break\n2:30 PM - Workshop: Machine Learning Fundamentals\n4:00 PM - Networking Session\n5:30 PM - Closing Remarks',
                'registration_deadline' => Carbon::now()->addDays(10),
                'waitlist_enabled' => true,
                'chat_opens_at' => Carbon::now()->addDays(12),
                'created_by' => $user->id,
            ],
            [
                'title' => 'Machine Learning Fundamentals Workshop',
                'description' => 'Hands-on workshop covering essential ML algorithms and practical implementation techniques. Perfect for beginners and intermediate practitioners looking to enhance their skills.',
                'start_date' => Carbon::now()->addDays(20)->setTime(10, 0),
                'end_date' => Carbon::now()->addDays(20)->setTime(16, 0),
                'location' => 'Alexandria University, Faculty of Engineering, Alexandria, Egypt',
                'max_attendees' => 50,
                'agenda' => '10:00 AM - Introduction to ML\n11:00 AM - Linear Regression Hands-on\n12:00 PM - Lunch Break\n1:00 PM - Classification Algorithms\n2:30 PM - Coffee Break\n3:00 PM - Practical Project\n4:30 PM - Q&A Session',
                'registration_deadline' => Carbon::now()->addDays(15),
                'waitlist_enabled' => false,
                'chat_opens_at' => Carbon::now()->addDays(18),
                'created_by' => $user->id,
            ],
            [
                'title' => 'Cairo Data Professionals Meetup',
                'description' => 'Monthly networking event for data professionals to share experiences and build connections. This casual meetup is perfect for networking and knowledge sharing.',
                'start_date' => Carbon::now()->addDays(28)->setTime(18, 0),
                'end_date' => Carbon::now()->addDays(28)->setTime(21, 0),
                'location' => 'TechHub Cairo, New Cairo, Egypt',
                'max_attendees' => 100,
                'agenda' => '6:00 PM - Welcome & Introductions\n6:30 PM - Lightning Talks\n7:30 PM - Networking & Refreshments\n8:30 PM - Group Discussions\n9:00 PM - Closing',
                'registration_deadline' => Carbon::now()->addDays(25),
                'waitlist_enabled' => true,
                'chat_opens_at' => Carbon::now()->addDays(26),
                'created_by' => $user->id,
            ],
            [
                'title' => 'Deep Learning Bootcamp',
                'description' => 'Intensive 3-day bootcamp covering neural networks, CNNs, RNNs, and transformer architectures. This comprehensive program is designed for intermediate to advanced practitioners.',
                'start_date' => Carbon::now()->addDays(35)->setTime(9, 0),
                'end_date' => Carbon::now()->addDays(37)->setTime(17, 0),
                'location' => 'Giza Technology Park, Giza, Egypt',
                'max_attendees' => 30,
                'agenda' => 'Day 1: Neural Networks Fundamentals\nDay 2: CNNs and Computer Vision\nDay 3: RNNs and Natural Language Processing\nEach day: 9:00 AM - 5:00 PM with breaks',
                'registration_deadline' => Carbon::now()->addDays(30),
                'waitlist_enabled' => true,
                'chat_opens_at' => Carbon::now()->addDays(32),
                'created_by' => $user->id,
            ],
            [
                'title' => 'Data Visualization Summit',
                'description' => 'Explore the art and science of data visualization with industry experts. Learn about the latest tools, techniques, and best practices in data visualization.',
                'start_date' => Carbon::now()->addDays(42)->setTime(8, 30),
                'end_date' => Carbon::now()->addDays(42)->setTime(19, 0),
                'location' => 'Cairo Marriott Hotel, Zamalek, Cairo, Egypt',
                'max_attendees' => 200,
                'agenda' => '8:30 AM - Registration\n9:00 AM - Keynote: The Power of Visual Storytelling\n10:30 AM - Panel: Tools & Technologies\n12:00 PM - Lunch\n1:30 PM - Workshop Sessions\n3:30 PM - Coffee Break\n4:00 PM - Case Studies\n5:30 PM - Networking Reception',
                'registration_deadline' => Carbon::now()->addDays(35),
                'waitlist_enabled' => true,
                'chat_opens_at' => Carbon::now()->addDays(40),
                'created_by' => $user->id,
            ],
            [
                'title' => 'Blockchain & Data Analytics Meetup',
                'description' => 'Discover how blockchain technology is revolutionizing data analytics. Join us for an evening of learning and networking with industry professionals.',
                'start_date' => Carbon::now()->addDays(48)->setTime(19, 0),
                'end_date' => Carbon::now()->addDays(48)->setTime(22, 0),
                'location' => 'Innovation Hub, New Administrative Capital, Egypt',
                'max_attendees' => 80,
                'agenda' => '7:00 PM - Welcome & Registration\n7:30 PM - Presentation: Blockchain in Data Analytics\n8:15 PM - Panel Discussion\n9:00 PM - Networking & Refreshments\n10:00 PM - Closing',
                'registration_deadline' => Carbon::now()->addDays(45),
                'waitlist_enabled' => false,
                'chat_opens_at' => Carbon::now()->addDays(46),
                'created_by' => $user->id,
            ],
        ];

        foreach ($events as $eventData) {
            Event::create($eventData);
        }
    }
}
