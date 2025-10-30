<?php

namespace App\Filament\Widgets;

use App\Models\JobApplication;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentJobApplicationsPending extends BaseWidget
{
    protected static ?string $heading = 'Recent Job Applications (Pending)';

    protected int|string|array $columnSpan = [
        'xl' => 6,
    ];

    protected static ?int $sort = 6;

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->query($this->getQuery())
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Applicant')
                    ->searchable(),
                Tables\Columns\TextColumn::make('jobListing.title')
                    ->label('Job')
                    ->limit(30),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->label('Status')
                    ->color(fn ($record) => $record->status_color ?? null),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('M d, Y')
                    ->label('Applied')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated(false)
            ->recordUrl(fn ($record) => route('filament.admin.resources.job-applications.edit', $record));
    }

    protected function getQuery(): Builder
    {
        return JobApplication::query()
            ->with(['user', 'jobListing'])
            ->pending()
            ->latest()
            ->limit(5);
    }
}


