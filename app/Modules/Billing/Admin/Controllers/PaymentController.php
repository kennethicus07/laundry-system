<?php

namespace App\Modules\Billing\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Billing\Admin\Actions\RecordCashPayment;
use App\Modules\Billing\Admin\Requests\RecordCashPaymentRequest;
use App\Modules\Billing\Admin\Resources\PaymentResource;
use App\Modules\Billing\Exceptions\BillingException;
use App\Modules\Billing\Models\Invoice;
use App\Modules\Billing\Models\Payment;
use App\Modules\Billing\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    private const PER_PAGE = 20;

    /**
     * Read-only queue of QR payments awaiting verification.
     * Verify / reject actions are added with the QR backend step.
     */
    public function pendingQr(): Response
    {
        $payments = Payment::query()
            ->qr()
            ->pendingVerification()
            ->with('invoice:id,invoice_number')
            ->oldest('received_at')
            ->oldest('id')
            ->paginate(self::PER_PAGE);

        return Inertia::render('Billing/Admin/Payments/PendingQr', [
            'payments' => PaymentResource::collection($payments),
        ]);
    }

    public function storeCash(
        RecordCashPaymentRequest $request,
        Invoice $invoice,
        RecordCashPayment $action,
    ): RedirectResponse {
        try {
            $payment = $action->execute(
                invoiceId: $invoice->id,
                amount: Money::of($request->validated('amount')),
                staffId: $request->user()->id,
                idempotencyKey: $request->validated('idempotency_key'),
            );
        } catch (BillingException $e) {
            throw ValidationException::withMessages(['amount' => $e->getMessage()]);
        }

        return back()->with(
            'success',
            'Cash payment of '.Money::of($payment->amount)->format().' recorded.'
        );
    }
}