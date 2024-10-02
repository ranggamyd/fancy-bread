<?php

namespace App\Filament\Resources\ProductResource\Widgets;

use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use App\Filament\Resources\ProductResource\Pages\ListProducts;

class ProductStats extends BaseWidget
{
    use InteractsWithPageTable;

    protected static ?string $pollingInterval = null;

    protected function getTablePage(): string
    {
        return ListProducts::class;
    }

    protected function getStats(): array
    {
        return [
            Stat::make('Low stock', $this->getPageTableQuery()->whereColumn('stock', '<=', 'security_stock')->count() . '/' . $this->getPageTableQuery()->count() . ' Items'),
            Stat::make('Qty items', $this->getPageTableQuery()->sum('stock')),
            Stat::make('Average price', 'IDR ' . number_format($this->getPageTableQuery()->avg('post_tax_price'), 2, '.', ',')),
        ];
    }
}
