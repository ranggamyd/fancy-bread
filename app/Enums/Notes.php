<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum Notes: string implements HasColor, HasIcon, HasLabel
{
    case BelumTTF = 'belum_ttf';

    case SudahTTF = 'sudah_ttf';

    public function getLabel(): string
    {
        return match ($this) {
            self::BelumTTF => 'Belum TTF',
            self::SudahTTF => 'Sudah TTF',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::BelumTTF => 'warning',
            self::SudahTTF => 'success',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::BelumTTF => 'heroicon-m-stop-circle',
            self::SudahTTF => 'heroicon-m-check-circle',
        };
    }
}
