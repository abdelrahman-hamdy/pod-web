<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InternshipApplicationResource\Pages;
use App\Models\InternshipApplication;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InternshipApplicationResource extends Resource
{
    protected static ?string $model = InternshipApplication::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Internships';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Applicant Information')
                    ->schema([
                        Forms\Components\TextInput::make('full_name')->readOnly(),
                        Forms\Components\TextInput::make('email')->readOnly(),
                        Forms\Components\TextInput::make('phone')->readOnly(),
                        Forms\Components\Textarea::make('motivation')->readOnly()->columnSpanFull(),
                        Forms\Components\KeyValue::make('portfolio_links')
                            ->label('Portfolio Links')
                            ->keyLabel('Platform')
                            ->valueLabel('URL')
                            ->deletable(false)
                            ->addable(false)
                            ->reorderable(false)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Admin fields')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'reviewing' => 'Reviewing',
                                'accepted' => 'Accepted',
                                'rejected' => 'Rejected',
                            ])
                            ->required(),
                        Forms\Components\RichEditor::make('admin_response')->columnSpanFull(),
                        Forms\Components\Textarea::make('admin_notes')->columnSpanFull(),
                    ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name')->searchable(),
                Tables\Columns\TextColumn::make('internship.title')->label('Internship')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('user.email')->label('Applicant Email')->searchable(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'reviewing' => 'Reviewing',
                        'accepted' => 'Accepted',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('accept')
                    ->label('Accept')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (InternshipApplication $record) {
                        $record->update([
                            'status' => 'accepted',
                            'admin_id' => auth()->id(),
                        ]);
                        \Filament\Notifications\Notification::make()
                            ->title('Application Accepted')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (InternshipApplication $record) => in_array($record->status, ['pending', 'under_review'])),

                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->action(function (InternshipApplication $record, array $data) {
                        $record->update([
                            'status' => 'rejected',
                            'admin_id' => auth()->id(),
                            'admin_response' => $data['rejection_reason'],
                        ]);
                        \Filament\Notifications\Notification::make()
                            ->title('Application Rejected')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (InternshipApplication $record) => in_array($record->status, ['pending', 'under_review'])),

                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListInternshipApplications::route('/'),
            'edit' => Pages\EditInternshipApplication::route('/{record}/edit'),
        ];
    }
}
