<?php

namespace App\Filament\Resources\UserResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use App\Filament\Exports\UserExporter;
use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\ManageRecords;

class ManageUsers extends ManageRecords
{
    protected static string $resource = UserResource::class;

    protected function getActions(): array
    {
        return [ExportAction::make()->exporter(UserExporter::class), CreateAction::make()];
    }
}
