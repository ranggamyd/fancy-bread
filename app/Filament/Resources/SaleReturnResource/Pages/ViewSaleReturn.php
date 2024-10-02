<?php

namespace App\Filament\Resources\SaleReturnResource\Pages;

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\SaleReturnResource;

class ViewSaleReturn extends ViewRecord
{
    protected static string $resource = SaleReturnResource::class;

    protected function getHeaderActions(): array
    {
        return [EditAction::make()];
    }
}
