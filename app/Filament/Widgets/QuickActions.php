<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class QuickActions extends Widget
{

    protected static ?string $heading = 'Quick Actions';

    protected static string $view = 'filament.widgets.quick-actions';

    protected static ?int $sort = -1;

    protected int|string|array $columnSpan = [
        'xl' => 12,
    ];

    public function getQuickActions(): array
    {
        return [
            [
                'label' => 'Post Job',
                'icon' => 'heroicon-m-briefcase',
                'color' => 'success',
                'url' => route('filament.admin.resources.job-listings.create'),
            ],
            [
                'label' => 'Create Event',
                'icon' => 'heroicon-m-calendar-days',
                'color' => 'warning',
                'url' => route('filament.admin.resources.events.create'),
            ],
            [
                'label' => 'New Hackathon',
                'icon' => 'heroicon-m-code-bracket',
                'color' => 'info',
                'url' => route('filament.admin.resources.hackathons.create'),
            ],
            [
                'label' => 'Manage Users',
                'icon' => 'heroicon-m-user-group',
                'color' => 'gray',
                'classes' => 'text-gray-900 bg-gray-100 hover:bg-gray-200 border border-gray-200',
                'url' => route('filament.admin.resources.users.index'),
            ],
        ];
    }
}


