<?php

namespace App\Filament\Resources\SaleResource\Widgets;

use App\Enums\Status;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use App\Filament\Resources\SaleResource\Pages\ListSales;

class SaleStats extends BaseWidget
{
    use InteractsWithPageTable;

    protected static ?string $pollingInterval = null;

    protected function getTablePage(): string
    {
        return ListSales::class;
    }

    protected function getStats(): array
    {
        $new = $this->getPageTableQuery()->where('status', Status::New)->whereBetween('date', [now()->firstOfMonth(), now()->endOfMonth()])->count();
        $returned = $this->getPageTableQuery()->where('status', Status::Returned)->whereBetween('date', [now()->firstOfMonth(), now()->endOfMonth()])->count();
        $total = $this->getPageTableQuery()->whereBetween('date', [now()->firstOfMonth(), now()->endOfMonth()])->count();

        $lastMonth_revenue = $this->getPageTableQuery()->whereBetween('date', [now()->subMonth()->firstOfMonth(), now()->subMonth()->endOfMonth()])->sum('grandtotal');
        $current_revenue = $this->getPageTableQuery()->whereBetween('date', [now()->firstOfMonth(), now()->endOfMonth()])->sum('grandtotal');

        if ($current_revenue >= $lastMonth_revenue) {
            $description = number_format($current_revenue - $lastMonth_revenue, 2, '.', ',') . ' increase';
        } else {
            $description = number_format($lastMonth_revenue - $current_revenue, 2, '.', ',') . ' decrease';
        }

        return [
            Stat::make('Current month', $new . ' / ' . $total)
                ->description("{$new} from {$total} sales are undelivered"),
            Stat::make('Returned', $returned),
            Stat::make('Expected Revenue', 'IDR ' . number_format($current_revenue, 2, '.', ','))
                ->description($description)
                ->descriptionIcon($current_revenue >= $lastMonth_revenue ? "heroicon-m-arrow-trending-up" : "heroicon-m-arrow-trending-down", IconPosition::Before)
                ->color($current_revenue >= $lastMonth_revenue ? "success" : "danger"),
        ];
    }
}
