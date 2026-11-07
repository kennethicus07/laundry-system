<?php

namespace App\Modules\Billing\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Billing\Admin\Resources\RefundResource;
use App\Modules\Billing\Enums\RefundStatus;
use App\Modules\Billing\Models\Refund;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class RefundController extends Controller
{
    private const PER_PAGE = 20;

    /**
     * Read-only refund list. Request / approve / reject / complete
     * actions are added with the refund backend step.
     */
    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::enum(RefundStatus::class)],
        ]);

        $refunds = Refund::query()
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->with([
                'payment:id,invoice_id,method,amount',
                'payment.invoice:id,invoice_number',
                'requestedBy:id,name',
                'approvedBy:id,name',
            ])
            ->latest('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return Inertia::render('Billing/Admin/Refunds/Index', [
            'refunds' => RefundResource::collection($refunds),
            'filters' => [
                'status' => $filters['status'] ?? null,
            ],
        ]);
    }
}