<?php

namespace App\Filament\Resources\PurchaseResource\Pages;

use App\Models\Purchase;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\PurchaseResource;

class ViewPurchase extends ViewRecord
{
    protected static string $resource = PurchaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('print_invoice')
                ->label('Invoice')
                ->icon('heroicon-o-printer')
                ->url(fn(Purchase $record): string => route('purchases.invoice.print', $record))
                ->openUrlInNewTab()
                ->color('primary'),

            EditAction::make()
        ];
    }
}
