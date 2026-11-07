<?php

namespace App\Modules\Billing\Services;

use App\Modules\Billing\DTO\InvoiceBalance;
use App\Modules\Billing\Enums\PaymentStatus;
use App\Modules\Billing\Models\Invoice;
use App\Modules\Billing\Support\Money;

class InvoiceBalanceCalculator
{
    /**
     * Always queries fresh rows (never cached relations), so it is safe to
     * call inside a transaction after locking the invoice.
     */
    public function calculate(Invoice $invoice): InvoiceBalance
    {
        $payments = $invoice->payments()
            ->whereIn('status', [
                PaymentStatus::Completed->value,
                PaymentStatus::PendingVerification->value,
            ])
            ->with(['refunds' => fn ($query) => $query->completed()])
            ->get();

        $completed = $payments->filter->isCompleted();
        $pending = $payments->filter->isPending();

        return new InvoiceBalance(
            total: Money::of($invoice->total_amount),
            paid: Money::sum($completed->pluck('amount')),
            refunded: Money::sum($completed->flatMap->refunds->pluck('amount')),
            pending: Money::sum($pending->pluck('amount')),
        );
    }

    /**
     * Recalculates and persists the invoice status. Void invoices are untouched.
     */
    public function syncStatus(Invoice $invoice): InvoiceBalance
    {
        $balance = $this->calculate($invoice);

        if ($invoice->isVoid()) {
            return $balance;
        }

        $invoice->status = $balance->status();

        if ($invoice->isDirty('status')) {
            $invoice->save();
        }

        return $balance;
    }
}