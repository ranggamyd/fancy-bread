<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum Status: string implements HasColor, HasIcon, HasLabel
{
    case New = 'new';

    case Delivered = 'delivered';

    case Returned = 'returned';

    public function getLabel(): string
    {
        return match ($this) {
            self::New => 'New',
            self::Delivered => 'Delivered',
            self::Returned => 'Returned',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::New => 'info',
            self::Delivered => 'success',
            self::Returned => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::New => 'heroicon-m-sparkles',
            self::Delivered => 'heroicon-m-check-badge',
            self::Returned => 'heroicon-m-arrow-uturn-left',
        };
    }
}
