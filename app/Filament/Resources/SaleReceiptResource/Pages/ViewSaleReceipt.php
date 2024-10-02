<?php

namespace App\Filament\Resources\SaleReceiptResource\Pages;

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\SaleReceiptResource;

class ViewSaleReceipt extends ViewRecord
{
    protected static string $resource = SaleReceiptResource::class;

    protected function getHeaderActions(): array
    {
        return [EditAction::make()];
    }
}
