<?php

namespace App\Filament\Resources\PurchaseResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Exports\PurchaseExporter;
use App\Filament\Resources\PurchaseResource;

class ListPurchases extends ListRecords
{
    protected static string $resource = PurchaseResource::class;

    protected function getHeaderWidgets(): array
    {
        return PurchaseResource::getWidgets();
    }

    protected function getHeaderActions(): array
    {
        return [ExportAction::make()->exporter(PurchaseExporter::class), CreateAction::make()];
    }
}
