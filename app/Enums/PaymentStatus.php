<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case PENDING = 'pending';
    case RECEIPT_PENDING = 'receipt_pending';
    case RECEIPT_RECEIVED = 'receipt_received';
    case UNDER_VERIFICATION = 'under_verification';
    case PARTIALLY_PAID = 'partially_paid';
    case COMPLETED = 'completed';
    case REFUNDED = 'refunded';
    case CANCELLED = 'cancelled';
    case FAILED = 'failed';
}
