<?php

namespace App\Modules\Processing\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Processing\Admin\Actions\GetProcessingOrders;
use App\Modules\Processing\Admin\Actions\StartProcessingOrder;
use App\Modules\Processing\Admin\Actions\CompleteProcessingOrder;
use DomainException;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProcessingController extends Controller
{
    public function index(
        Request $request,
        GetProcessingOrders $action,
    ): Response {
        $validated = $request->validate([
            'tab' => ['nullable', 'string', 'in:awaiting_processing,in_progress,ready_for_delivery,history'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $tab = $validated['tab'] ?? 'awaiting_processing';
        $search = $validated['search'] ?? '';

        return Inertia::render('Processing/Admin/Index', [
            'orders' => $action->execute($tab, $search),
            'activeTab' => $tab,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

        public function start(
        int $order,
        StartProcessingOrder $action,
    ): RedirectResponse {
        try {
            $action->execute($order);
        } catch (DomainException $exception) {
            throw ValidationException::withMessages([
                'order' => $exception->getMessage(),
            ]);
        }

        return back()->with('success', 'Laundry processing has started.');
    }

    public function complete(
        int $order,
        CompleteProcessingOrder $action,
    ): RedirectResponse {
        try {
            $action->execute($order);
        } catch (DomainException $exception) {
            throw ValidationException::withMessages([
                'order' => $exception->getMessage(),
            ]);
        }

        return back()->with('success', 'Laundry processing has been completed.');
    }
}

