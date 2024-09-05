<?php

namespace App\Filament\Resources\PurchaseResource\Pages;

use Filament\Actions;
use App\Models\Purchase;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\PurchaseResource;
use App\Filament\Resources\PurchaseReturnResource;

class ViewPurchase extends ViewRecord
{
    protected static string $resource = PurchaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('print_invoice')
                ->label('Invoice')
                ->icon('heroicon-o-printer')
                ->url(fn(Purchase $record): string => route('purchases.invoice.print', $record))
                ->openUrlInNewTab()
                ->color('primary'),
            Actions\Action::make('return')
                ->label('Return')
                ->icon('heroicon-o-arrow-uturn-left')
                ->url(fn(Purchase $record): string => PurchaseReturnResource::getUrl('create', ['purchase_id' => $record->id]))
                ->openUrlInNewTab()
                ->hidden(fn(Purchase $record) => $record->purchaseReturns->count() > 0)
                ->color('danger'),
            Actions\EditAction::make()
        ];
    }
}
