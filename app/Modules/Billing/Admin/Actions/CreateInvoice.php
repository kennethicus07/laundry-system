<?php

namespace App\Modules\Billing\Admin\Actions;

use App\Modules\Billing\Enums\InvoiceStatus;
use App\Modules\Billing\Exceptions\BillingException;
use App\Modules\Billing\Models\Invoice;
use App\Modules\Billing\Support\Money;
use App\Modules\Orders\Contracts\BillableOrder;
use App\Modules\Orders\DTO\BillableOrderData;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CreateInvoice
{
    private const LOCK_SECONDS = 10;
    private const LOCK_WAIT_SECONDS = 5;

    public function __construct(
        private BillableOrder $orders,
    ) {}

    public function execute(int $orderId): Invoice
    {
        // Throws if the order is not yet received or has no items.
        $order = $this->orders->findForBilling($orderId);

        // lockForUpdate() cannot lock a row that does not exist yet, so two
        // simultaneous requests could both create an invoice. An atomic cache
        // lock per order serializes creation.
        $lock = Cache::lock("billing:create-invoice:order:{$order->id}", self::LOCK_SECONDS);

        try {
            return $lock->block(self::LOCK_WAIT_SECONDS, fn () => $this->create($order));
        } catch (LockTimeoutException) {
            throw BillingException::invoiceCreationInProgress();
        }
    }

    private function create(BillableOrderData $order): Invoice
    {
        return DB::transaction(function () use ($order) {
            $existing = Invoice::query()
                ->forOrder($order->id)
                ->active()
                ->first();

            if ($existing) {
                throw BillingException::activeInvoiceExists($existing->invoice_number);
            }

            $items = $order->items->map(fn (array $item) => [
                'description' => $item['description'],
                'quantity' => (string) $item['quantity'],
                'unit_price' => Money::of($item['unit_price'])->toDecimal(),
                'line_total' => Money::of($item['line_total'])->toDecimal(),
            ]);

            $subtotal = Money::sum($items->pluck('line_total'));

            $invoice = Invoice::create([
                'invoice_number' => Invoice::generateNumber(),
                'order_id' => $order->id,
                'subtotal' => $subtotal->toDecimal(),
                'discount_amount' => Money::zero()->toDecimal(),
                'tax_amount' => Money::zero()->toDecimal(),
                'total_amount' => $subtotal->toDecimal(),
                'status' => InvoiceStatus::Unpaid,
                'revision_number' => Invoice::INITIAL_REVISION_NUMBER,
                'issued_at' => now(),
            ]);

            $invoice->items()->createMany($items->all());

            return $invoice->load('items');
        });
    }
}