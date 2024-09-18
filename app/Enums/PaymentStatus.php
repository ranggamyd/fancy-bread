<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum PaymentStatus: string implements HasColor, HasIcon, HasLabel
{
    case Unpaid = 'unpaid';
    
    case Uncomplete = 'uncomplete';
    
    case Paid = 'paid';

    public function getLabel(): string
    {
        return match ($this) {
            self::Unpaid => 'Unpaid',
            self::Uncomplete => 'Uncomplete',
            self::Paid => 'Paid',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Unpaid => 'danger',
            self::Uncomplete => 'warning',
            self::Paid => 'success',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Unpaid => 'heroicon-m-x-circle',
            self::Uncomplete => 'heroicon-m-stop-circle',
            self::Paid => 'heroicon-m-check-circle',
        };
    }
}
