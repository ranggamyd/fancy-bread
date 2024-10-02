<?php

namespace App\Filament\Resources\VendorResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use App\Filament\Exports\VendorExporter;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\VendorResource;

class ListVendors extends ListRecords
{
    protected static string $resource = VendorResource::class;

    protected function getHeaderActions(): array
    {
        return [ExportAction::make()->exporter(VendorExporter::class), CreateAction::make()];
    }
}
