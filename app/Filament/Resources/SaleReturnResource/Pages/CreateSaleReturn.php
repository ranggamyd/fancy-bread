<?php

namespace App\Filament\Resources\SaleReturnResource\Pages;

use App\Models\Sale;
use App\Enums\Status;
use App\Models\SaleReceipt;
use App\Models\SaleReceiptReturn;
use Illuminate\Support\Facades\Auth;
use App\Filament\Resources\SaleResource;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\SaleReturnResource;

class CreateSaleReturn extends CreateRecord
{
    protected static string $resource = SaleReturnResource::class;

    protected static bool $canCreateAnother = false;

    public function beforeFill()
    {
        $saleIds = explode(',', request('sales'));
        if ($saleIds[0] === "") redirect(SaleResource::getUrl('index'));
    }

    protected function afterCreate(): void
    {
        $saleReturn = $this->record;

        foreach ($saleReturn->saleReturnInvoices as $item) {
            $sale = Sale::find($item->sale_id);
            $sale->status = Status::Returned;
            $sale->save();

            $saleReceipt = SaleReceipt::whereHas('saleReceiptInvoices', fn($q) => $q->where('sale_id', $sale->id))->first();
            SaleReceiptReturn::create([
                'sale_receipt_id' => $saleReceipt->id,
                'sale_return_id' => $saleReturn->id,
            ]);

            $saleReceipt->return_items += $saleReturn->total_items;
            $saleReceipt->total_return += $saleReturn->grandtotal;
            $saleReceipt->subtotal = $saleReceipt->subtotal - $saleReturn->subtotal;
            $saleReceipt->grandtotal = $saleReceipt->grandtotal - $saleReturn->subtotal;
            $saleReceipt->save();
        }

        Notification::make()
            ->icon('heroicon-o-arrow-uturn-left')
            ->title("#{$saleReturn->code}")
            ->body("Sale returned from : {$saleReturn->saleReturnInvoices->count()} invoices.")
            ->actions([Action::make('Detail')->url(SaleReturnResource::getUrl('view', ['record' => $sale]))])
            ->sendToDatabase(Auth::user());
    }

    protected function getRedirectUrl(): string
    {
        return SaleResource::getUrl('index');
    }
}
