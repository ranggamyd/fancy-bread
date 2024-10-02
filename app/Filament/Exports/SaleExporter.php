<?php

namespace App\Filament\Exports;

use App\Models\Sale;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class SaleExporter extends Exporter
{
    protected static ?string $model = Sale::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')->label('ID'),
            ExportColumn::make('code'),
            ExportColumn::make('invoice'),
            ExportColumn::make('goods_receipt_number')->label('No. LPB'),
            ExportColumn::make('customer.name'),
            ExportColumn::make('notes')->enabledByDefault(false),
            ExportColumn::make('status'),
            ExportColumn::make('total_items'),
            ExportColumn::make('subtotal'),
            ExportColumn::make('shipping_price'),
            ExportColumn::make('total_discount'),
            ExportColumn::make('grandtotal'),
            ExportColumn::make('date'),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your sale export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
