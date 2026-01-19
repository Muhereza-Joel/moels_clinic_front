<?php

namespace App\Enums;

enum SupplierRating: string
{
    case EXCELLENT = 'excellent';
    case GOOD = 'good';
    case AVERAGE = 'average';
    case POOR = 'poor';

    public static function labels(): array
    {
        return [
            self::EXCELLENT->value => 'Excellent',
            self::GOOD->value => 'Good',
            self::AVERAGE->value => 'Average',
            self::POOR->value => 'Poor',
        ];
    }

    public function color(): string
    {
        return match ($this) {
            self::EXCELLENT => 'success',
            self::GOOD => 'info',
            self::AVERAGE => 'warning',
            self::POOR => 'danger',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::EXCELLENT => 'heroicon-o-star',
            self::GOOD => 'heroicon-o-thumb-up',
            self::AVERAGE => 'heroicon-o-minus',
            self::POOR => 'heroicon-o-thumb-down',
        };
    }
}
