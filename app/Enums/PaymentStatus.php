<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case PENDING = 'pending';
    case PARTIAL = 'partial';
    case PAID = 'paid';
    case OVERDUE = 'overdue';

    public static function labels(): array
    {
        return [
            self::PENDING->value => 'Pending',
            self::PARTIAL->value => 'Partial',
            self::PAID->value => 'Paid',
            self::OVERDUE->value => 'Overdue',
        ];
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::PARTIAL => 'info',
            self::PAID => 'success',
            self::OVERDUE => 'danger',
        };
    }
}
