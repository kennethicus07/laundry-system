<?php

namespace App\Modules\Billing\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case Qr = 'qr';
}