<?php

namespace App\Filament\Resources\SaleResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use App\Filament\Exports\SaleExporter;
use Filament\Resources\Components\Tab;
use App\Filament\Resources\SaleResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Pages\Concerns\ExposesTableToWidgets;

class ListSales extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = SaleResource::class;

    protected function getHeaderWidgets(): array
    {
        return SaleResource::getWidgets();
    }

    public function getTabs(): array
    {
        return [
            null => Tab::make('All'),
            'new' => Tab::make()->query(fn ($query) => $query->where('status', 'new')),
            'delivered' => Tab::make()->query(fn ($query) => $query->where('status', 'delivered')),
            'returned' => Tab::make()->query(fn ($query) => $query->where('status', 'returned')),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [ExportAction::make()->exporter(SaleExporter::class), CreateAction::make()];
    }
}
