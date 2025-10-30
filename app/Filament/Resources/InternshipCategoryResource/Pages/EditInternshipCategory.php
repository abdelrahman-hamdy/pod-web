<?php

namespace App\Filament\Resources\InternshipCategoryResource\Pages;

use App\Filament\Resources\InternshipCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInternshipCategory extends EditRecord
{
    protected static string $resource = InternshipCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
