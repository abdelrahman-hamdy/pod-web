<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DebugNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:debug {user_id? : The ID of the user to check notifications for}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug notification system and display notification data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Debugging Notification System...');
        $this->newLine();

        // Check table structure
        $this->info('📋 Checking notifications table structure:');
        $columns = DB::select('SHOW COLUMNS FROM notifications');

        $hasViewedAt = false;
        foreach ($columns as $column) {
            $marker = $column->Field === 'viewed_at' ? '✓' : ' ';
            $this->line("  [{$marker}] {$column->Field} ({$column->Type})");
            if ($column->Field === 'viewed_at') {
                $hasViewedAt = true;
            }
        }

        if (!$hasViewedAt) {
            $this->warn('⚠️  WARNING: viewed_at column is missing! Run: php artisan migrate');
        }

        $this->newLine();

        // Check notification count
        $totalCount = DB::table('notifications')->count();
        $this->info("📊 Total notifications in database: {$totalCount}");

        if ($totalCount === 0) {
            $this->warn('⚠️  No notifications found in database!');
            $this->info('💡 Create a test notification by liking a post or performing another action.');
            return 0;
        }

        // Get user ID
        $userId = $this->argument('user_id');
        if (!$userId) {
            // Get first user with notifications
            $notification = DB::table('notifications')->first();
            $userId = $notification?->notifiable_id;
        }

        if ($userId) {
            $this->newLine();
            $this->info("👤 Checking notifications for user ID: {$userId}");

            $userNotifications = DB::table('notifications')
                ->where('notifiable_id', $userId)
                ->where('notifiable_type', 'App\\Models\\User')
                ->orderBy('created_at', 'desc')
                ->get();

            $this->info("   Total: {$userNotifications->count()}");
            $unread = $userNotifications->whereNull('read_at')->count();
            $unviewed = $hasViewedAt ? $userNotifications->whereNull('viewed_at')->count() : 'N/A';
            $this->info("   Unread: {$unread}");
            $this->info("   Unviewed: {$unviewed}");

            if ($userNotifications->count() > 0) {
                $this->newLine();
                $this->info('📝 Recent notifications:');

                foreach ($userNotifications->take(5) as $index => $notification) {
                    $data = json_decode($notification->data, true);
                    $body = $data['body'] ?? $data['message'] ?? 'No message';
                    $type = $data['type'] ?? 'unknown';
                    $readStatus = $notification->read_at ? '✓ read' : '✗ unread';

                    $this->line("  {$index}. [{$readStatus}] {$type}: {$body}");
                    $this->line("     Created: {$notification->created_at}");
                }
            }
        }

        $this->newLine();
        $this->info('✅ Debugging complete!');

        return 0;
    }
}
