<?php

namespace App\Filament\Resources\SaleReceiptResource\Pages;

use Filament\Actions\ExportAction;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Exports\SaleReceiptExporter;
use App\Filament\Resources\SaleReceiptResource;
use Filament\Pages\Concerns\ExposesTableToWidgets;

class ListSaleReceipts extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = SaleReceiptResource::class;

    protected function getHeaderWidgets(): array
    {
        return SaleReceiptResource::getWidgets();
    }

    protected function getHeaderActions(): array
    {
        return [ExportAction::make()->exporter(SaleReceiptExporter::class)];
    }
}
