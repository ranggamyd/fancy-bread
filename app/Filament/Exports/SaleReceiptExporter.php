<?php

namespace App\Filament\Exports;

use App\Models\SaleReceipt;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class SaleReceiptExporter extends Exporter
{
    protected static ?string $model = SaleReceipt::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')->label('ID'),
            ExportColumn::make('code'),
            ExportColumn::make('branch'),
            ExportColumn::make('saleReceiptInvoices.invoice'),
            ExportColumn::make('saleReceiptReturns.code'),
            ExportColumn::make('notes')->enabledByDefault(false),
            ExportColumn::make('invoice_items'),
            ExportColumn::make('total_invoice'),
            ExportColumn::make('return_items'),
            ExportColumn::make('total_return'),
            ExportColumn::make('subtotal'),
            ExportColumn::make('fee'),
            ExportColumn::make('grandtotal'),
            ExportColumn::make('payment_status'),
            ExportColumn::make('date'),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your sale receipt export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
