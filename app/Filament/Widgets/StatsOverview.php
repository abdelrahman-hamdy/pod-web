<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use App\Models\Hackathon;
use App\Models\JobListing;
use App\Models\Post;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = -2;

    protected int|string|array $columnSpan = [
        'xl' => 12,
    ];

    protected function getStats(): array
    {
        return [
            Stat::make('Total Users', User::count())
                ->description('Registered members')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),

            Stat::make('Business Accounts', User::query()->where('role', 'client')->count())
                ->description('Companies / clients')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('primary'),

            Stat::make('Active Posts', Post::count())
                ->description('Community posts shared')
                ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                ->color('info'),

            Stat::make('Events', Event::count())
                ->description('Total events created')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('warning'),

            Stat::make('Job Listings', JobListing::count())
                ->description('Active job opportunities')
                ->descriptionIcon('heroicon-m-briefcase')
                ->color('primary'),

            Stat::make('Hackathons', Hackathon::count())
                ->description('Competitions available')
                ->descriptionIcon('heroicon-m-code-bracket')
                ->color('danger'),
        ];
    }
}
