<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewsletterSubscriptionResource\Pages;
use App\Models\NewsletterSubscription;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class NewsletterSubscriptionResource extends Resource
{
    protected static ?string $model = NewsletterSubscription::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationLabel = 'Newsletter Subscriptions';

    protected static ?string $navigationGroup = 'Content';

    protected static ?int $navigationSort = 100;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->disabled(fn (?NewsletterSubscription $record) => $record !== null),
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable(),
                Forms\Components\TextInput::make('token')
                    ->required()
                    ->maxLength(255)
                    ->default(fn () => \Illuminate\Support\Str::random(32))
                    ->disabled(),
                Forms\Components\DateTimePicker::make('subscribed_at')
                    ->required()
                    ->default(now()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn (?string $state, NewsletterSubscription $record) => $state ?? 'Guest'),
                Tables\Columns\IconColumn::make('user_id')
                    ->label('Account Type')
                    ->boolean()
                    ->trueIcon('heroicon-o-user')
                    ->falseIcon('heroicon-o-user-minus')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->getStateUsing(fn (NewsletterSubscription $record) => $record->user_id !== null),
                Tables\Columns\TextColumn::make('subscribed_at')
                    ->label('Subscribed')
                    ->dateTime()
                    ->sortable()
                    ->since(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('has_account')
                    ->label('Has Account')
                    ->query(fn (Builder $query) => $query->whereNotNull('user_id')),
                Tables\Filters\Filter::make('guest_only')
                    ->label('Guests Only')
                    ->query(fn (Builder $query) => $query->whereNull('user_id')),
                Tables\Filters\Filter::make('subscribed_after')
                    ->form([
                        Forms\Components\DatePicker::make('subscribed_from')
                            ->label('Subscribed After'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['subscribed_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('subscribed_at', '>=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make()
                    ->label('Unsubscribe'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Unsubscribe Selected'),
                ]),
            ])
            ->defaultSort('subscribed_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNewsletterSubscriptions::route('/'),
        ];
    }
}
