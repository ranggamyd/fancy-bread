<?php

namespace App\Filament\Exports;

use App\Models\DriverSchedule;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class DriverScheduleExporter extends Exporter
{
    protected static ?string $model = DriverSchedule::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')->label('ID'),
            ExportColumn::make('driver.name'),
            ExportColumn::make('sale.invoice'),
            ExportColumn::make('sale.customer.name'),
            ExportColumn::make('sale.customer.short_address'),
            ExportColumn::make('notes')->enabledByDefault(false),
            ExportColumn::make('sale.shipping_price'),
            ExportColumn::make('date'),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your driver schedule export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
