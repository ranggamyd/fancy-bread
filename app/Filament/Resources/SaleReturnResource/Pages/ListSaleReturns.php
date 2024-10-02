<?php

namespace App\Filament\Resources\SaleReturnResource\Pages;

use Filament\Actions\ExportAction;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Exports\SaleReturnExporter;
use App\Filament\Resources\SaleReturnResource;

class ListSaleReturns extends ListRecords
{
    protected static string $resource = SaleReturnResource::class;

    protected function getHeaderActions(): array
    {
        return [ExportAction::make()->exporter(SaleReturnExporter::class)];
    }
}
