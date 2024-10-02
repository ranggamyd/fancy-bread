<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Exports\CategoryExporter;
use App\Filament\Resources\CategoryResource;

class ListCategories extends ListRecords
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [ExportAction::make()->exporter(CategoryExporter::class), CreateAction::make()];
    }
}
