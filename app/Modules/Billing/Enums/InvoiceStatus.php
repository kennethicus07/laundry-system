<?php

namespace App\Modules\Billing\Enums;

enum InvoiceStatus: string
{
    case Unpaid = 'unpaid';
    case PartiallyPaid = 'partially_paid';
    case Paid = 'paid';
    case Void = 'void';
}