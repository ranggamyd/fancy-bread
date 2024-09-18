<?php

namespace App\Filament\Resources\SaleResource\Pages;

use Filament\Actions;
use Filament\Resources\Components\Tab;
use App\Filament\Resources\SaleResource;
use Filament\Resources\Pages\ListRecords;

class ListSales extends ListRecords
{
    protected static string $resource = SaleResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
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
}
