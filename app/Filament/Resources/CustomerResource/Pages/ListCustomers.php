<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Exports\CustomerExporter;
use App\Filament\Resources\CustomerResource;

class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [ExportAction::make()->exporter(CustomerExporter::class), CreateAction::make()];
    }
}
