<?php

namespace App\Filament\Resources\InternshipCategoryResource\Pages;

use App\Filament\Resources\InternshipCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInternshipCategories extends ListRecords
{
    protected static string $resource = InternshipCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
