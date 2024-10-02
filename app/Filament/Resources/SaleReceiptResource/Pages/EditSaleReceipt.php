<?php

namespace App\Filament\Resources\SaleReceiptResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\SaleReceiptResource;

class EditSaleReceipt extends EditRecord
{
    protected static string $resource = SaleReceiptResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
