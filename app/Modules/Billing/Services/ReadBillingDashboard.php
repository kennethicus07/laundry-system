<?php

namespace App\Modules\Billing\Services;

use App\Modules\Billing\Contracts\BillingDashboardReader;
use App\Modules\Billing\Enums\InvoiceStatus;
use App\Modules\Billing\Enums\PaymentStatus;
use App\Modules\Billing\Enums\RefundStatus;
use App\Modules\Billing\Models\Invoice;
use App\Modules\Billing\Models\Payment;
use App\Modules\Billing\Models\Refund;

class ReadBillingDashboard implements BillingDashboardReader
{
    public function getBillingSummary(): array
    {
        $completedPayments = Payment::query()
            ->where('status', PaymentStatus::Completed->value);

        $pendingPayments = Payment::query()
            ->where('status', PaymentStatus::PendingVerification->value);

        $completedRefunds = Refund::query()
            ->where('status', RefundStatus::Completed->value);

        $unpaidInvoices = Invoice::query()
            ->whereIn('status', [
                InvoiceStatus::Unpaid->value,
                InvoiceStatus::PartiallyPaid->value,
            ]);

        return [
            'completed_payments_count' => $completedPayments->count(),

            'completed_payments_amount' => (string) (
                $completedPayments->sum('amount') ?? '0.00'
            ),

            'pending_verification_count' => $pendingPayments->count(),

            'pending_verification_amount' => (string) (
                $pendingPayments->sum('amount') ?? '0.00'
            ),

            'unpaid_invoices_count' => $unpaidInvoices->count(),

            'completed_refunds_count' => $completedRefunds->count(),

            'completed_refunds_amount' => (string) (
                $completedRefunds->sum('amount') ?? '0.00'
            ),
        ];
    }
}