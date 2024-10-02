<?php

namespace App\Filament\Resources\ProductResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use App\Filament\Exports\ProductExporter;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\ProductResource;
use Filament\Pages\Concerns\ExposesTableToWidgets;

class ListProducts extends ListRecords
{
    use ExposesTableToWidgets;
    
    protected static string $resource = ProductResource::class;

    protected function getHeaderWidgets(): array
    {
        return ProductResource::getWidgets();
    }

    protected function getHeaderActions(): array
    {
        return [ExportAction::make()->exporter(ProductExporter::class), CreateAction::make()];
    }
}
