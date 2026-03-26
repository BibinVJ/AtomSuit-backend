<?php

namespace App\Enums;

enum DeliveryNoteStatus: string
{
    case DISPATCHED = 'DISPATCHED';
    case DELIVERED = 'DELIVERED';
    case VOIDED = 'VOIDED';

    public function label(): string
    {
        return match ($this) {
            self::DISPATCHED => 'Dispatched',
            self::DELIVERED => 'Delivered',
            self::VOIDED => 'Voided',
        };
    }
}
