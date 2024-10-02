<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use App\Models\SaleReceipt;
use App\Models\SaleReceiptPayment;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        $lastMonth_revenue = SaleReceipt::whereBetween('date', [now()->subMonth()->firstOfMonth(), now()->subMonth()->endOfMonth()])->sum('grandtotal');
        $current_revenue = SaleReceipt::whereBetween('date', [now()->firstOfMonth(), now()->endOfMonth()])->sum('grandtotal');

        if ($current_revenue >= $lastMonth_revenue) {
            $description = number_format($current_revenue - $lastMonth_revenue, 2, '.', ',') . ' increase';
        } else {
            $description = number_format($lastMonth_revenue - $current_revenue, 2, '.', ',') . ' decrease';
        }

        $paid = SaleReceiptPayment::whereBetween('date', [now()->firstOfMonth(), now()->endOfMonth()])->sum('total');

        return [
            Stat::make('Low stock', Product::whereColumn('stock', '<=', 'security_stock')->count() . '/' . Product::count() . ' Items'),
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
