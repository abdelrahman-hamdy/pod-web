<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('first_name')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('last_name')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(255),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Profile Details')
                    ->schema([
                        Forms\Components\Textarea::make('bio')
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('avatar')
                            ->label('Avatar')
                            ->image()
                            ->directory('avatars')
                            ->disk('public')
                            ->visibility('public')
                            ->maxSize(2048)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg', 'image/webp'])
                            ->imageEditor()
                            ->imageCropAspectRatio('1:1')
                            ->imageResizeTargetWidth('400')
                            ->imageResizeTargetHeight('400')
                            ->imagePreviewHeight('200')
                            ->loadingIndicatorPosition('center')
                            ->default(function ($record) {
                                if (! $record) {
                                    return null;
                                }

                                $avatar = $record->avatar;
                                if (! $avatar || $avatar === '' || $avatar === 'null') {
                                    return null;
                                }

                                // If it's an external URL (OAuth), return as-is
                                if (str_starts_with($avatar, 'http://') || str_starts_with($avatar, 'https://')) {
                                    return $avatar;
                                }

                                // Convert /storage/avatars/filename.jpg to avatars/filename.jpg
                                if (str_starts_with($avatar, '/storage/avatars/')) {
                                    $filename = basename($avatar);
                                    $relativePath = 'avatars/'.$filename;

                                    // Return relative path - Filament will handle URL generation
                                    return Storage::disk('public')->exists($relativePath) ? $relativePath : null;
                                }

                                // If it already starts with avatars/, return as-is if exists
                                if (str_starts_with($avatar, 'avatars/')) {
                                    return Storage::disk('public')->exists($avatar) ? $avatar : null;
                                }

                                // Otherwise, prepend avatars/
                                $relativePath = 'avatars/'.$avatar;

                                return Storage::disk('public')->exists($relativePath) ? $relativePath : null;
                            })
                            ->helperText('Upload a square image. Max size: 2MB. Supported formats: JPEG, PNG, WebP.'),
                        Forms\Components\TextInput::make('gender'),
                        Forms\Components\DatePicker::make('birthday'),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Location')
                    ->schema([
                        Forms\Components\TextInput::make('city')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('country')
                            ->maxLength(255),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Professional Information')
                    ->schema([
                        Forms\Components\TextInput::make('skills'),
                        Forms\Components\TextInput::make('experience_level'),
                        Forms\Components\TextInput::make('education'),
                        Forms\Components\TextInput::make('portfolio_links')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Social Links')
                    ->schema([
                        Forms\Components\TextInput::make('linkedin_url')
                            ->url()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('github_url')
                            ->url()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('twitter_url')
                            ->url()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('website_url')
                            ->url()
                            ->maxLength(255),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Account Settings')
                    ->schema([
                        Forms\Components\Select::make('role')
                            ->label('User Role')
                            ->options([
                                'user' => 'Regular User',
                                'client' => 'Client/Business',
                                'admin' => 'Administrator',
                                'superadmin' => 'Super Administrator',
                            ])
                            ->required()
                            ->default('user')
                            ->disabled(fn ($record) => $record && auth()->user()?->role !== 'superadmin')
                            ->helperText(fn ($record) => $record && auth()->user()?->role !== 'superadmin' ? 'Only super administrators can change user roles.' : 'Select the user role.')
                            ->visible(fn () => auth()->user()?->isAdmin() ?? false),
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->maxLength(255)
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create'),
                        Forms\Components\Toggle::make('profile_completed'),
                        Forms\Components\Toggle::make('profile_onboarding_seen'),
                        Forms\Components\Toggle::make('is_active'),
                        Forms\Components\DateTimePicker::make('email_verified_at'),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('OAuth/Provider Information')
                    ->schema([
                        Forms\Components\TextInput::make('google_id')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('linkedin_id')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('provider')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('provider_id')
                            ->maxLength(255),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar')
                    ->label('Avatar')
                    ->circular()
                    ->getStateUsing(function ($record) {
                        if ($record->avatar && $record->avatar !== '' && $record->avatar !== 'null') {
                            // External URL (OAuth providers)
                            if (str_starts_with($record->avatar, 'http://') || str_starts_with($record->avatar, 'https://')) {
                                return $record->avatar;
                            }

                            // Already a full storage path (starts with /storage/)
                            if (str_starts_with($record->avatar, '/storage/')) {
                                return asset($record->avatar);
                            }

                            // Relative path - use Storage::url()
                            return Storage::url($record->avatar);
                        }

                        return null;
                    })
                    ->defaultImageUrl(function ($record) {
                        // Use getAvatarColor() to get Tailwind class string (e.g., 'bg-indigo-100 text-indigo-600')
                        $colorClass = $record->getAvatarColor();
                        $bgColor = self::tailwindClassToHex($colorClass);
                        $textColor = self::extractTextColorFromClass($colorClass);

                        $initials = strtoupper(substr($record->name ?? 'U', 0, 2));
                        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40"><circle cx="20" cy="20" r="20" fill="'.htmlspecialchars($bgColor).'"/><text x="20" y="20" font-size="16" fill="'.htmlspecialchars($textColor).'" text-anchor="middle" dominant-baseline="central" font-weight="600">'.htmlspecialchars($initials).'</text></svg>';

                        return 'data:image/svg+xml;base64,'.base64_encode($svg);
                    })
                    ->size(40),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('first_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('last_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('city')
                    ->searchable(),
                Tables\Columns\TextColumn::make('country')
                    ->searchable(),
                Tables\Columns\TextColumn::make('gender'),
                Tables\Columns\TextColumn::make('birthday')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('experience_level'),
                Tables\Columns\TextColumn::make('linkedin_url')
                    ->searchable(),
                Tables\Columns\TextColumn::make('github_url')
                    ->searchable(),
                Tables\Columns\TextColumn::make('twitter_url')
                    ->searchable(),
                Tables\Columns\TextColumn::make('website_url')
                    ->searchable(),
                Tables\Columns\IconColumn::make('profile_completed')
                    ->boolean(),
                Tables\Columns\IconColumn::make('profile_onboarding_seen')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'superadmin' => 'danger',
                        'admin' => 'warning',
                        'client' => 'info',
                        'user' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'superadmin' => 'Super Admin',
                        'admin' => 'Administrator',
                        'client' => 'Client/Business',
                        'user' => 'Regular User',
                        default => ucfirst($state),
                    })
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    /**
     * Determine whether the user can view any models.
     */
    public static function canViewAny(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    /**
     * Determine whether the user can create models.
     */
    public static function canCreate(): bool
    {
        // Only superadmin can create users
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        if (! $record instanceof User) {
            return false;
        }

        $user = auth()->user();

        if (! $user || ! $user->isAdmin()) {
            return false;
        }

        // Superadmins can update any user
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Regular admins can update non-admin users (cannot update other admins or superadmins)
        if ($user->hasRole('admin')) {
            return ! in_array($record->role, ['admin', 'superadmin']);
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        if (! $record instanceof User) {
            return false;
        }

        $user = auth()->user();

        if (! $user || ! $user->isSuperAdmin()) {
            return false;
        }

        // Prevent superadmins from deleting themselves
        if ($user->id === $record->id) {
            return false;
        }

        // Only superadmins can delete users
        return true;
    }

    /**
     * Convert Tailwind bg-*-100 class to hex color.
     */
    private static function tailwindClassToHex(string $tailwindClass): string
    {
        // Extract color name from bg-*-100 pattern
        if (preg_match('/bg-(\w+)-100/', $tailwindClass, $matches)) {
            $colorName = $matches[1];

            // Map Tailwind color names to their hex values for *-100 shade
            $colorMap = [
                'indigo' => '#e0e7ff',
                'purple' => '#f3e8ff',
                'pink' => '#fce7f3',
                'blue' => '#dbeafe',
                'green' => '#dcfce7',
                'yellow' => '#fef9c3',
                'red' => '#fee2e2',
                'orange' => '#ffedd5',
                'teal' => '#ccfbf1',
                'cyan' => '#cffafe',
                'emerald' => '#d1fae5',
                'lime' => '#ecfccb',
                'amber' => '#fef3c7',
                'rose' => '#fff1f2',
                'violet' => '#ede9fe',
                'fuchsia' => '#fae8ff',
                'sky' => '#e0f2fe',
                'stone' => '#f5f5f4',
                'neutral' => '#f5f5f5',
                'zinc' => '#f4f4f5',
                'slate' => '#f1f5f9',
            ];

            return $colorMap[$colorName] ?? '#e2e8f0';
        }

        return '#e2e8f0'; // Default slate-100
    }

    /**
     * Extract text color from Tailwind class string.
     */
    private static function extractTextColorFromClass(string $tailwindClass): string
    {
        // Extract text color from text-*-600 pattern
        if (preg_match('/text-(\w+)-(\d+)/', $tailwindClass, $matches)) {
            $colorName = $matches[1];
            $shade = (int) $matches[2];

            // Map Tailwind color names to hex for *-600 shade
            $colorMap600 = [
                'indigo' => '#4f46e5',
                'purple' => '#9333ea',
                'pink' => '#db2777',
                'blue' => '#2563eb',
                'green' => '#16a34a',
                'yellow' => '#ca8a04',
                'red' => '#dc2626',
                'orange' => '#ea580c',
                'teal' => '#0d9488',
                'cyan' => '#0891b2',
                'emerald' => '#059669',
                'lime' => '#65a30d',
                'amber' => '#d97706',
                'rose' => '#e11d48',
                'violet' => '#7c3aed',
                'fuchsia' => '#c026d3',
                'sky' => '#0284c7',
                'stone' => '#78716c',
                'neutral' => '#525252',
                'zinc' => '#52525b',
                'slate' => '#475569',
            ];

            return $colorMap600[$colorName] ?? '#64748b';
        }

        return '#64748b'; // Default slate-600
    }
}
