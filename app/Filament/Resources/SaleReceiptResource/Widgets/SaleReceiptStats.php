<?php

namespace App\Filament\Resources\SaleReceiptResource\Widgets;

use App\Enums\PaymentStatus;
use App\Models\SaleReceiptPayment;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use App\Filament\Resources\SaleReceiptResource\Pages\ListSaleReceipts;

class SaleReceiptStats extends BaseWidget
{
    use InteractsWithPageTable;

    protected static ?string $pollingInterval = null;

    protected function getTablePage(): string
    {
        return ListSaleReceipts::class;
    }

    protected function getStats(): array
    {
        $unpaid = $this->getPageTableQuery()->where('payment_status', '!=', PaymentStatus::Paid)->whereBetween('date', [now()->firstOfMonth(), now()->endOfMonth()])->count();
        $total = $this->getPageTableQuery()->whereBetween('date', [now()->firstOfMonth(), now()->endOfMonth()])->count();

        $lastMonth_revenue = $this->getPageTableQuery()->whereBetween('date', [now()->subMonth()->firstOfMonth(), now()->subMonth()->endOfMonth()])->sum('grandtotal');
        $current_revenue = $this->getPageTableQuery()->whereBetween('date', [now()->firstOfMonth(), now()->endOfMonth()])->sum('grandtotal');

        if ($current_revenue >= $lastMonth_revenue) {
            $description = number_format($current_revenue - $lastMonth_revenue, 2, '.', ',') . ' increase';
        } else {
            $description = number_format($lastMonth_revenue - $current_revenue, 2, '.', ',') . ' decrease';
        }

        $paid = SaleReceiptPayment::whereBetween('date', [now()->firstOfMonth(), now()->endOfMonth()])->sum('total');

        return [
            Stat::make('Current month', $unpaid . ' / ' . $total)
                ->description("{$unpaid} from {$total} receipts are unpaid"),
            Stat::make('Expected Revenue', 'IDR ' . number_format($current_revenue, 2, '.', ','))
                ->description($description)
                ->descriptionIcon($current_revenue >= $lastMonth_revenue ? "heroicon-m-arrow-trending-up" : "heroicon-m-arrow-trending-down", IconPosition::Before)
                ->color($current_revenue >= $lastMonth_revenue ? "success" : "danger"),
            Stat::make('Paid', 'IDR ' . number_format($paid, 2, '.', ','))
                ->description((($current_revenue > 0) ? round(($paid / $current_revenue) * 100, 2) : 0) . '% paid')
                ->descriptionIcon("heroicon-o-banknotes", IconPosition::Before)
                ->color("success"),
        ];
    }
}
