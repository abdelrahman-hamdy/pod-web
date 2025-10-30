<?php

namespace App\Filament\Widgets;

use App\Models\InternshipApplication;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentInternshipApplications extends BaseWidget
{
    protected static ?string $heading = 'Recent Internship Applications';

    protected int|string|array $columnSpan = [
        'xl' => 6,
    ];

    protected static ?int $sort = 4;

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->query($this->getQuery())
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Applicant')
                    ->searchable(),
                Tables\Columns\TextColumn::make('internship.title')
                    ->label('Internship')
                    ->limit(30),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->label('Status'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('M d, Y')
                    ->label('Applied')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated(false)
            ->recordUrl(fn ($record) => route('filament.admin.resources.internship-applications.edit', $record));
    }

    protected function getQuery(): Builder
    {
        return InternshipApplication::query()
            ->with(['user', 'internship'])
            ->latest()
            ->limit(5);
    }
}


