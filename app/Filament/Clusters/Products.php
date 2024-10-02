<?php

namespace App\Filament\Clusters;

use App\Models\Product;
use Filament\Clusters\Cluster;

class Products extends Cluster
{
    protected static ?string $navigationGroup = 'Fancy Master';

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    public static function getNavigationBadge(): ?string
    {
        return (string) Product::whereColumn('stock', '<=', 'security_stock')->count();
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Low stock products';
    }
}
