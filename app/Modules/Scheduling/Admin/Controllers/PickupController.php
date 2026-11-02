<?php

namespace App\Modules\Scheduling\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Scheduling\Admin\Actions\CompletePickup;
use App\Modules\Scheduling\Admin\Actions\ListMyPickups;
use App\Modules\Scheduling\Admin\Actions\MarkPickupMissed;
use App\Modules\Scheduling\Admin\Actions\StartPickup;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class PickupController extends Controller
{
    public function index(
        Request $request,
        ListMyPickups $action
    ): Response {
        return Inertia::render('Pickups/Admin/Index', [
            'pickups' => $action->execute(
                (int) $request->user()->id
            ),
        ]);
    }

    public function start(
        Request $request,
        int $schedule,
        StartPickup $action
    ): RedirectResponse {
        try {
            $action->execute(
                $schedule,
                (int) $request->user()->id
            );
        } catch (DomainException $exception) {
            return back()->withErrors([
                'pickup' => $exception->getMessage(),
            ]);
        }

        return back()->with(
            'success',
            'Pickup started successfully.'
        );
    }

    public function complete(
        Request $request,
        int $schedule,
        CompletePickup $action
    ): RedirectResponse {
        try {
            $action->execute(
                $schedule,
                (int) $request->user()->id
            );
        } catch (DomainException $exception) {
            return back()->withErrors([
                'pickup' => $exception->getMessage(),
            ]);
        }

        return back()->with(
            'success',
            'Pickup completed successfully.'
        );
    }

    public function missed(
        Request $request,
        int $schedule,
        MarkPickupMissed $action
    ): RedirectResponse {
        $validated = $request->validate([
            'outcome_notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        try {
            $action->execute(
                $schedule,
                (int) $request->user()->id,
                $validated['outcome_notes'] ?? null
            );
        } catch (DomainException $exception) {
            return back()->withErrors([
                'pickup' => $exception->getMessage(),
            ]);
        }

        return back()->with(
            'success',
            'Pickup marked as missed.'
        );
    }
}
