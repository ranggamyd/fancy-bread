<?php

namespace App\Filament\Resources\DriverResource\Pages;

use App\Filament\Exports\DriverExporter;
use App\Filament\Resources\DriverResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageDrivers extends ManageRecords
{
    protected static string $resource = DriverResource::class;

    protected function getActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
