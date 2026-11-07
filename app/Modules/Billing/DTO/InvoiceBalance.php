<?php

namespace App\Modules\Billing\DTO;

use App\Modules\Billing\Enums\InvoiceStatus;
use App\Modules\Billing\Support\Money;

/**
 * Snapshot of an invoice's money position at a point in time.
 */
final readonly class InvoiceBalance
{
    public function __construct(
        public Money $total,
        public Money $paid,       // completed payments
        public Money $refunded,   // completed refunds on completed payments
        public Money $pending,    // QR payments awaiting verification
    ) {}

    public function netPaid(): Money
    {
        return $this->paid->minus($this->refunded);
    }

    /** What the customer still owes (negative = overpaid). */
    public function balance(): Money
    {
        return $this->total->minus($this->netPaid());
    }

    /**
     * What can be accepted right now without risking an overpayment
     * once pending QR payments are verified.
     */
    public function payable(): Money
    {
        return $this->balance()->minus($this->pending)->atLeastZero();
    }

    public function isOverpaid(): bool
    {
        return $this->balance()->isNegative();
    }

    public function status(): InvoiceStatus
    {
        if (! $this->balance()->isPositive()) {
            return InvoiceStatus::Paid;
        }

        if ($this->netPaid()->isPositive()) {
            return InvoiceStatus::PartiallyPaid;
        }

        return InvoiceStatus::Unpaid;
    }

    public function toArray(): array
    {
        return [
            'total' => $this->total->toDecimal(),
            'paid' => $this->paid->toDecimal(),
            'refunded' => $this->refunded->toDecimal(),
            'net_paid' => $this->netPaid()->toDecimal(),
            'pending' => $this->pending->toDecimal(),
            'balance' => $this->balance()->toDecimal(),
            'payable' => $this->payable()->toDecimal(),
        ];
    }
}