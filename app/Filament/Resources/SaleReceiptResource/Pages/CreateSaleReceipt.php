<?php

namespace App\Filament\Resources\SaleReceiptResource\Pages;

use Illuminate\Support\Facades\Auth;
use App\Filament\Resources\SaleResource;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\SaleReceiptResource;

class CreateSaleReceipt extends CreateRecord
{
    protected static string $resource = SaleReceiptResource::class;

    protected static bool $canCreateAnother = false;

    public function beforeFill()
    {
        $saleIds = explode(',', request('sales'));
        if ($saleIds[0] === "") redirect(SaleResource::getUrl('index'));
    }

    protected function afterCreate(): void
    {
        $saleReceipt = $this->record;

        Notification::make()
            ->icon('heroicon-o-banknotes')
            ->title("#{$saleReceipt->code}")
            ->body("New sale's receipt from : {$saleReceipt->saleReceiptInvoices->count()} invoices & {$saleReceipt->saleReceiptReturns->count()} returns.")
            ->actions([Action::make('Detail')->url(SaleReceiptResource::getUrl('view', ['record' => $saleReceipt]))])
            ->sendToDatabase(Auth::user());
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
