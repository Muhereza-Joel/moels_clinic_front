<?php

namespace App\Enums;

enum PurchaseOrderStatus: string
{
    case DRAFT = 'draft';
    case PENDING_APPROVAL = 'pending_approval';
    case APPROVED = 'approved';
    case ORDERED = 'ordered';
    case PARTIALLY_RECEIVED = 'partially_received';
    case FULLY_RECEIVED = 'fully_received';
    case CANCELLED = 'cancelled';
    case CLOSED = 'closed';

    public static function labels(): array
    {
        return [
            self::DRAFT->value => 'Draft',
            self::PENDING_APPROVAL->value => 'Pending Approval',
            self::APPROVED->value => 'Approved',
            self::ORDERED->value => 'Ordered',
            self::PARTIALLY_RECEIVED->value => 'Partially Received',
            self::FULLY_RECEIVED->value => 'Fully Received',
            self::CANCELLED->value => 'Cancelled',
            self::CLOSED->value => 'Closed',
        ];
    }

    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::PENDING_APPROVAL => 'warning',
            self::APPROVED => 'info',
            self::ORDERED => 'primary',
            self::PARTIALLY_RECEIVED => 'indigo',
            self::FULLY_RECEIVED => 'success',
            self::CANCELLED => 'danger',
            self::CLOSED => 'secondary',
        };
    }
}
