<?php

namespace App\Filament\Resources\BrandResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\BrandResource;
use Filament\Resources\Pages\ListRecords;

class ListBrands extends ListRecords
{
    protected static string $resource = BrandResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
