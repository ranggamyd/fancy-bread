<?php

namespace App\Filament\Resources\PurchaseResource\Pages;

use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\PurchaseResource;

class CreatePurchase extends CreateRecord
{
    protected static string $resource = PurchaseResource::class;

    protected function afterCreate(): void
    {
        $purchase = $this->record;

        Notification::make()
            ->icon('heroicon-o-shopping-cart')
            ->title("#{$purchase->invoice}")
            ->body("New purchase from : {$purchase->vendor->name} - {$purchase->vendor->short_address}.")
            ->actions([Action::make('Detail')->url(PurchaseResource::getUrl('view', ['record' => $purchase]))])
            ->sendToDatabase(Auth::user());
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
