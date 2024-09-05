<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Models\Sale;
use Filament\Actions;
use App\Filament\Resources\SaleResource;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\SaleReturnResource;

class ViewSale extends ViewRecord
{
    protected static string $resource = SaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('print_invoice')
                ->label('Invoice')
                ->icon('heroicon-o-printer')
                ->url(fn(Sale $record): string => route('sales.invoice.print', $record))
                ->openUrlInNewTab()
                ->color('primary'),
            Actions\Action::make('return')
                ->label('Return')
                ->icon('heroicon-o-arrow-uturn-left')
                ->url(fn(Sale $record): string => SaleReturnResource::getUrl('create', ['sale_id' => $record->id]))
                ->openUrlInNewTab()
                ->hidden(fn(Sale $record) => $record->saleReturns->count() > 0)
                ->color('danger'),
            Actions\EditAction::make(),
        ];
    }
}
