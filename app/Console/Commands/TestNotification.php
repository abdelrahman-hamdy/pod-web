<?php

namespace App\Console\Commands;

use App\Models\User;
use App\NotificationType;
use App\Services\MobileNotificationService;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class TestNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification:test 
                            {user? : The user ID or email to send notification to}
                            {--type=post_liked : The notification type to test}
                            {--mobile : Send only mobile push notification}
                            {--all : Send to all users with FCM tokens}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test notification system by sending a sample notification';

    protected NotificationService $notificationService;
    protected MobileNotificationService $mobileNotificationService;

    /**
     * Create a new command instance.
     */
    public function __construct(
        NotificationService $notificationService,
        MobileNotificationService $mobileNotificationService
    ) {
        parent::__construct();
        $this->notificationService = $notificationService;
        $this->mobileNotificationService = $mobileNotificationService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Get user(s) to send notification to
        if ($this->option('all')) {
            $users = User::whereNotNull('fcm_token')->get();
            $this->info("Sending test notification to {$users->count()} users with FCM tokens...");
        } else {
            $userInput = $this->argument('user');
            
            if (!$userInput) {
                // Interactive mode
                $users = User::whereNotNull('fcm_token')->limit(10)->get();
                
                if ($users->isEmpty()) {
                    $this->error('No users with FCM tokens found.');
                    return Command::FAILURE;
                }
                
                $options = $users->map(function ($user) {
                    return "{$user->name} ({$user->email})";
                })->toArray();
                
                $selected = $this->choice('Select a user to send notification to:', $options);
                $index = array_search($selected, $options);
                $user = $users[$index];
            } else {
                // Find user by ID or email
                $user = User::where('id', $userInput)
                    ->orWhere('email', $userInput)
                    ->first();
                
                if (!$user) {
                    $this->error("User not found: {$userInput}");
                    return Command::FAILURE;
                }
            }
            
            $users = collect([$user]);
        }

        // Get notification type
        $typeString = $this->option('type');
        $type = NotificationType::tryFrom($typeString);
        
        if (!$type) {
            $availableTypes = array_map(fn($case) => $case->value, NotificationType::cases());
            $this->error("Invalid notification type: {$typeString}");
            $this->info("Available types: " . implode(', ', $availableTypes));
            return Command::FAILURE;
        }

        // Prepare test data
        $testData = $this->getTestData($type);
        
        // Send notifications
        $successCount = 0;
        $failCount = 0;
        
        foreach ($users as $user) {
            $this->info("Sending to: {$user->name} ({$user->email})");
            
            try {
                if ($this->option('mobile')) {
                    // Send only mobile notification
                    if (!$user->fcm_token) {
                        $this->warn("  ⚠ User has no FCM token");
                        $failCount++;
                        continue;
                    }
                    
                    $success = $this->mobileNotificationService->sendMobileNotification($user, $type, $testData);
                    
                    if ($success) {
                        $this->info("  ✓ Mobile notification sent successfully");
                        $successCount++;
                    } else {
                        $this->error("  ✗ Failed to send mobile notification");
                        $failCount++;
                    }
                } else {
                    // Send through normal notification service
                    $this->notificationService->send(
                        $user,
                        $type,
                        $testData,
                        ['database', 'push']
                    );
                    
                    $this->info("  ✓ Notification sent successfully");
                    $successCount++;
                }
            } catch (\Exception $e) {
                $this->error("  ✗ Error: " . $e->getMessage());
                $failCount++;
            }
        }

        // Summary
        $this->newLine();
        $this->info("=== Summary ===");
        $this->info("Success: {$successCount}");
        
        if ($failCount > 0) {
            $this->warn("Failed: {$failCount}");
        }
        
        // Check database notifications
        if (!$this->option('mobile')) {
            $this->newLine();
            $this->info("Checking database notifications...");
            
            foreach ($users as $user) {
                $count = $user->notifications()
                    ->where('created_at', '>=', now()->subMinutes(1))
                    ->count();
                
                $this->info("  {$user->name}: {$count} new notification(s)");
            }
        }

        return Command::SUCCESS;
    }

    /**
     * Get test data for notification type.
     */
    protected function getTestData(NotificationType $type): array
    {
        $faker = \Faker\Factory::create();
        
        $baseData = [
            'title' => 'Test Notification',
            'body' => "This is a test {$type->value} notification sent at " . now()->format('H:i:s'),
            'test_mode' => true,
        ];
        
        // Add type-specific data
        return match ($type) {
            NotificationType::POST_LIKED => array_merge($baseData, [
                'title' => 'Post Liked',
                'body' => $faker->name . ' liked your post',
                'post_id' => 1,
                'liker_id' => 2,
                'liker_name' => $faker->name,
                'avatar' => $faker->imageUrl(100, 100, 'people'),
            ]),
            
            NotificationType::COMMENT_ADDED => array_merge($baseData, [
                'title' => 'New Comment',
                'body' => $faker->name . ' commented on your post',
                'post_id' => 1,
                'comment_id' => 1,
                'commenter_id' => 2,
                'commenter_name' => $faker->name,
                'avatar' => $faker->imageUrl(100, 100, 'people'),
            ]),
            
            NotificationType::EVENT_REMINDER => array_merge($baseData, [
                'title' => 'Event Reminder',
                'body' => 'Your event "Tech Conference" starts in 1 hour',
                'event_id' => 1,
                'event_name' => 'Tech Conference',
                'event_time' => now()->addHour()->format('H:i'),
            ]),
            
            NotificationType::JOB_APPLICATION_RECEIVED => array_merge($baseData, [
                'title' => 'New Job Application',
                'body' => $faker->name . ' applied for Senior Developer position',
                'job_id' => 1,
                'application_id' => 1,
                'applicant_name' => $faker->name,
                'job_title' => 'Senior Developer',
            ]),
            
            NotificationType::MESSAGE_RECEIVED => array_merge($baseData, [
                'title' => 'New Message',
                'body' => $faker->name . ': ' . $faker->sentence,
                'sender_id' => 2,
                'sender_name' => $faker->name,
                'message_preview' => $faker->sentence,
            ]),
            
            NotificationType::HACKATHON_TEAM_INVITED => array_merge($baseData, [
                'title' => 'Team Invitation',
                'body' => 'You have been invited to join "Code Warriors" team',
                'hackathon_id' => 1,
                'team_id' => 1,
                'team_name' => 'Code Warriors',
                'inviter_name' => $faker->name,
            ]),
            
            default => $baseData,
        };
    }
}
