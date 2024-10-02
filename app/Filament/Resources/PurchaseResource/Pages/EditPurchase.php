<?php

namespace App\Filament\Resources\PurchaseResource\Pages;

use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\PurchaseResource;

class EditPurchase extends EditRecord
{
    protected static string $resource = PurchaseResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [ViewAction::make()];
    }
}
