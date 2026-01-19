<?php

namespace App\Enums;

enum DeliveryStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case SHIPPED = 'shipped';
    case PARTIALLY_DELIVERED = 'partially_delivered';
    case DELIVERED = 'delivered';
    case ON_TIME = 'on_time';
    case DELAYED = 'delayed';
    case CANCELLED = 'cancelled';

    public static function labels(): array
    {
        return [
            self::PENDING->value => 'Pending',
            self::PROCESSING->value => 'Processing',
            self::SHIPPED->value => 'Shipped',
            self::PARTIALLY_DELIVERED->value => 'Partially Delivered',
            self::DELIVERED->value => 'Delivered',
            self::ON_TIME->value => 'On Time',
            self::DELAYED->value => 'Delayed',
            self::CANCELLED->value => 'Cancelled',
        ];
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'gray',
            self::PROCESSING => 'blue',
            self::SHIPPED => 'indigo',
            self::PARTIALLY_DELIVERED => 'yellow',
            self::DELIVERED => 'green',
            self::ON_TIME => 'success',
            self::DELAYED => 'danger',
            self::CANCELLED => 'red',
        };
    }
}
