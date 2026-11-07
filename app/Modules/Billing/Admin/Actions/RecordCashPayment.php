<?php

namespace App\Modules\Billing\Admin\Actions;

use App\Modules\Billing\Enums\PaymentMethod;
use App\Modules\Billing\Enums\PaymentStatus;
use App\Modules\Billing\Exceptions\BillingException;
use App\Modules\Billing\Models\Invoice;
use App\Modules\Billing\Models\Payment;
use App\Modules\Billing\Services\InvoiceBalanceCalculator;
use App\Modules\Billing\Support\Money;
use Illuminate\Support\Facades\DB;

class RecordCashPayment
{
    public function __construct(
        private InvoiceBalanceCalculator $calculator,
    ) {}

    /**
     * Record the amount actually applied to the invoice, not the cash handed
     * over. (Customer gives ₱500 for a ₱450 balance → record ₱450, give change.)
     */
    public function execute(
        int $invoiceId,
        Money $amount,
        int $staffId,
        ?string $idempotencyKey = null,
    ): Payment {
        if (! $amount->isPositive()) {
            throw BillingException::amountMustBePositive();
        }

        return DB::transaction(function () use ($invoiceId, $amount, $staffId, $idempotencyKey) {
            $invoice = Invoice::query()
                ->lockForUpdate()
                ->findOrFail($invoiceId);

            // A retried request (double-click, network retry) returns the
            // original payment instead of recording the cash twice.
            if ($idempotencyKey !== null) {
                $existing = Payment::query()
                    ->where('idempotency_key', $idempotencyKey)
                    ->first();

                if ($existing) {
                    if ($existing->invoice_id !== $invoice->id) {
                        throw BillingException::idempotencyKeyReused();
                    }

                    return $existing;
                }
            }

            if ($invoice->isVoid()) {
                throw BillingException::invoiceIsVoid();
            }

            $balance = $this->calculator->calculate($invoice);

            if ($amount->greaterThan($balance->payable())) {
                throw BillingException::amountExceedsPayable($balance);
            }

            $now = now();

            $payment = $invoice->payments()->create([
                'amount' => $amount->toDecimal(),
                'method' => PaymentMethod::Cash,
                'status' => PaymentStatus::Completed,
                'idempotency_key' => $idempotencyKey,
                'recorded_by' => $staffId,
                'received_at' => $now,
                'paid_at' => $now,
            ]);

            $this->calculator->syncStatus($invoice);

            return $payment;
        });
    }
}