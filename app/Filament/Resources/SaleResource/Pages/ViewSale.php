<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Models\Sale;
use App\Enums\Status;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use App\Filament\Resources\SaleResource;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ViewRecord;

class ViewSale extends ViewRecord
{
    protected static string $resource = SaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('setLPB')
                ->label('Delivered?')
                ->icon('heroicon-m-check-badge')
                ->color('success')
                ->hidden(fn(Sale $record) => $record->status !== Status::New)
                ->modalHeading('Goods Receipt')
                ->form([
                    TextInput::make('goods_receipt_number')
                        ->label('No. LPB')
                        ->required()
                        ->default('LPB/' . strtotime(now()))
                        ->unique(ignoreRecord: true)
                        ->helperText('fill in this field would update the sale status to delivered.'),
                ])
                ->action(function ($record, $data) {
                    $record->goods_receipt_number = $data['goods_receipt_number'];
                    $record->status = Status::Delivered;
                    $record->save();
                }),

            Action::make('print_invoice')
                ->label('Invoice')
                ->icon('heroicon-o-printer')
                ->url(fn(Sale $record): string => route('sales.invoice.print', $record))
                ->openUrlInNewTab()
                ->color('primary'),

            EditAction::make(),
        ];
    }
}
