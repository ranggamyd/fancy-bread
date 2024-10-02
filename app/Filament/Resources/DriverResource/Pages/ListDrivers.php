<?php

namespace App\Filament\Resources\DriverResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use App\Filament\Exports\DriverExporter;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\DriverResource;

class ListDrivers extends ListRecords
{
    protected static string $resource = DriverResource::class;

    protected function getHeaderActions(): array
    {
        return [ExportAction::make()->exporter(DriverExporter::class), CreateAction::make()];
    }
}
