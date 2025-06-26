<?php

namespace App\Enums;

enum TransactionStatus: string
{
    case DRAFT = 'draft';
    case CONFIRMED = 'confirmed';
    case CONVERTED = 'converted';
    case COMPLETED = 'completed';
    case VOIDED = 'voided';
}
