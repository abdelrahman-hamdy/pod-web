<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserNotificationPreference;
use Illuminate\Database\Seeder;

class NotificationPreferencesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Setting up default notification preferences for users...');

        $users = User::leftJoin('user_notification_preferences', 'users.id', '=', 'user_notification_preferences.user_id')
            ->whereNull('user_notification_preferences.id')
            ->select('users.id')
            ->get();

        $progressBar = $this->command->getOutput()->createProgressBar($users->count());

        foreach ($users as $user) {
            UserNotificationPreference::create([
                'user_id' => $user->id,
                'email_notifications' => true,
                'push_notifications' => true,
                'in_app_notifications' => true,
                'notification_types' => [
                    'social' => true,
                    'events' => true,
                    'jobs' => true,
                    'hackathons' => true,
                    'internships' => true,
                    'messages' => true,
                    'account' => true,
                    'admin' => true,
                ],
            ]);

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->command->newLine();
        $this->command->info('Notification preferences created for ' . $users->count() . ' users.');
    }
}
