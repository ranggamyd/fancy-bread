<?php

namespace App\Filament\Resources\SaleReturnResource\Pages;

use App\Filament\Resources\SaleReturnResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSaleReturn extends EditRecord
{
    protected static string $resource = SaleReturnResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [Actions\ViewAction::make()];
    }
}
