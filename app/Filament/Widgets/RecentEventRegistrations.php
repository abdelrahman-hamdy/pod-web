<?php

namespace App\Filament\Widgets;

use App\Models\EventRegistration;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentEventRegistrations extends BaseWidget
{
    protected static ?string $heading = 'Recent Event Registrations';

    protected int|string|array $columnSpan = [
        'xl' => 6,
    ];

    protected static ?int $sort = 7;

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->query($this->getQuery())
            ->columns([
                Tables\Columns\TextColumn::make('event.title')
                    ->label('Event')
                    ->limit(30),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Registrant')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->label('Status'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('M d, Y')
                    ->label('Registered')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated(false)
            ->recordUrl(fn ($record) => route('filament.admin.resources.event-registrations.edit', $record));
    }

    protected function getQuery(): Builder
    {
        return EventRegistration::query()
            ->with(['event', 'user'])
            ->latest()
            ->limit(5);
    }
}


