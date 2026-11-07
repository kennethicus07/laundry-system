<?php

namespace App\Modules\Billing\Enums;

enum PaymentStatus: string
{
    case PendingVerification = 'pending_verification';
    case Completed = 'completed';
    case Failed = 'failed';
    case Cancelled = 'cancelled';
}