<?php

namespace App\Enums;

enum GoodsReceivedNoteStatus: string
{
    case DRAFT = 'DRAFT';
    case RECEIVED = 'RECEIVED';
    case CANCELLED = 'CANCELLED';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::RECEIVED => 'Received',
            self::CANCELLED => 'Cancelled',
        };
    }
}
