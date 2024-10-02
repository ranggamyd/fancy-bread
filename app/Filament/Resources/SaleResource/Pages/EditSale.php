<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Models\Sale;
use App\Enums\Status;
use Filament\Actions;
use App\Filament\Resources\SaleResource;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;

class EditSale extends EditRecord
{
    protected static string $resource = SaleResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('setLPB')
                ->label('Delivered?')
                ->icon('heroicon-m-check-badge')
                ->color('success')
                ->hidden(fn(Sale $record) => $record->status !== Status::New)
                ->modalHeading('Goods Receipt')
                ->form([
                    TextInput::make('goods_receipt_number')
                        ->label('No. LPB')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->helperText('fill in this field would update the sale status to delivered.'),
                ])
                ->action(function ($record, $data) {
                    $record->goods_receipt_number = $data['goods_receipt_number'];
                    $record->status = Status::Delivered;
                    $record->save();
                }),

            Actions\Action::make('print_invoice')
                ->label('Invoice')
                ->icon('heroicon-o-printer')
                ->url(fn(Sale $record): string => route('sales.invoice.print', $record))
                ->openUrlInNewTab()
                ->color('primary'),

            Actions\ViewAction::make()
        ];
    }
}
