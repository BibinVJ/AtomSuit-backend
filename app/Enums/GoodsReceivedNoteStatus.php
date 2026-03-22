<?php

namespace App\Enums;

enum GoodsReceivedNoteStatus: string
{
    case RECEIVED = 'RECEIVED';
    case VOIDED = 'VOIDED';

    public function label(): string
    {
        return match ($this) {
            self::RECEIVED => 'Received',
            self::VOIDED => 'Voided',
        };
    }
}
