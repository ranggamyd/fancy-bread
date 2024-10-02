<?php

namespace App\Filament\Resources\PurchaseResource\Widgets;

use App\Enums\Status;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use App\Filament\Resources\PurchaseResource\Pages\ListPurchases;

class PurchaseStats extends BaseWidget
{
    use InteractsWithPageTable;

    protected static ?string $pollingInterval = null;

    protected function getTablePage(): string
    {
        return ListPurchases::class;
    }

    protected function getStats(): array
    {
        $new = $this->getPageTableQuery()->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $total = $this->getPageTableQuery()->whereBetween('date', [now()->firstOfMonth(), now()->endOfMonth()])->count();

        $lastMonth_expense = $this->getPageTableQuery()->whereBetween('date', [now()->subMonth()->firstOfMonth(), now()->subMonth()->endOfMonth()])->sum('grandtotal');
        $current_expense = $this->getPageTableQuery()->whereBetween('date', [now()->firstOfMonth(), now()->endOfMonth()])->sum('grandtotal');

        if ($current_expense >= $lastMonth_expense) {
            $description = number_format($current_expense - $lastMonth_expense, 2, '.', ',') . ' increase';
        } else {
            $description = number_format($lastMonth_expense - $current_expense, 2, '.', ',') . ' decrease';
        }

        return [
            Stat::make('Current month', $total),
            Stat::make('New Puchase', $new),
            Stat::make('Expected Expense', 'IDR ' . number_format($current_expense, 2, '.', ',')),
        ];
    }
}
