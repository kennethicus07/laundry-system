<?php

namespace App\Modules\Billing\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Billing\Admin\Actions\CreateInvoice;
use App\Modules\Billing\Admin\Requests\StoreInvoiceRequest;
use App\Modules\Billing\Admin\Resources\InvoiceResource;
use App\Modules\Billing\Admin\Resources\InvoiceSummaryResource;
use App\Modules\Billing\Enums\InvoiceStatus;
use App\Modules\Billing\Models\Invoice;
use App\Modules\Billing\Services\InvoiceBalanceCalculator;
use App\Modules\Orders\Contracts\BillableOrder;
use App\Modules\Orders\DTO\BillableOrderSummary;
use DomainException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    private const PER_PAGE = 15;

    /** Candidates fetched from Orders, and how many are shown in the picker. */
    private const PICKER_FETCH = 50;
    private const PICKER_LIMIT = 20;

    public function __construct(
        private InvoiceBalanceCalculator $calculator,
    ) {}

    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::enum(InvoiceStatus::class)],
        ]);

        $invoices = Invoice::query()
            ->when($filters['search'] ?? null, function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('invoice_number', 'like', '%'.$search.'%');

                    if (ctype_digit($search)) {
                        $query->orWhere('order_id', (int) $search);
                    }
                });
            })
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->latest('issued_at')
            ->latest('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return Inertia::render('Billing/Admin/Invoices/Index', [
            'invoices' => InvoiceSummaryResource::collection($invoices),
            'filters' => [
                'search' => $filters['search'] ?? null,
                'status' => $filters['status'] ?? null,
            ],
        ]);
    }

    public function show(Invoice $invoice): Response
    {
        $invoice->load([
            'items',
            'payments' => fn ($query) => $query->latest('id')->with([
                'recordedBy:id,name',
                'verifiedBy:id,name',
                'refunds' => fn ($query) => $query->latest('id')->with([
                    'requestedBy:id,name',
                    'approvedBy:id,name',
                ]),
            ]),
            'revisions.items',
            'revisions.revisedBy:id,name',
        ]);

        return Inertia::render('Billing/Admin/Invoices/Show', [
            'invoice' => (new InvoiceResource($invoice))->resolve(),
            'balance' => $this->calculator->calculate($invoice)->toArray(),
        ]);
    }

    /**
     * JSON for the Create invoice picker: billable orders without an active invoice.
     */
    public function billableOrders(Request $request, BillableOrder $orders): JsonResponse
    {
        $search = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
        ])['search'] ?? null;

        $candidates = $orders->searchForBilling($search, self::PICKER_FETCH);

        $alreadyInvoiced = Invoice::query()
            ->active()
            ->whereIn('order_id', $candidates->pluck('id'))
            ->pluck('order_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $available = $candidates
            ->reject(fn (BillableOrderSummary $order) => in_array($order->id, $alreadyInvoiced, true))
            ->take(self::PICKER_LIMIT)
            ->map(fn (BillableOrderSummary $order) => $order->toArray())
            ->values();

        return response()->json(['data' => $available]);
    }

    public function store(StoreInvoiceRequest $request, CreateInvoice $action): RedirectResponse
    {
        try {
            $invoice = $action->execute($request->integer('order_id'));
        } catch (ModelNotFoundException) {
            throw ValidationException::withMessages(['order_id' => 'Order not found.']);
        } catch (DomainException $e) {
            // BillingException and the Orders reader's rule violations.
            throw ValidationException::withMessages(['order_id' => $e->getMessage()]);
        }

        return to_route('admin.invoices.show', $invoice)
            ->with('success', "Invoice {$invoice->invoice_number} created.");
    }
}