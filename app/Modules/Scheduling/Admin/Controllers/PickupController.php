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
use Illuminate\Support\Facades\Storage;
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
        $request->validate([
            'photo' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ]);

        $path = $request->file('photo')->store(
            'pickup-proofs',
            'public'
        );

        try {
            $action->execute(
                $schedule,
                (int) $request->user()->id,
                $path,
            );
        } catch (DomainException $exception) {
            Storage::disk('public')->delete($path);

            return back()->withErrors([
                'pickup' => $exception->getMessage(),
            ]);
        } catch (\Throwable $exception) {
            // Don't leave orphaned photos if anything else fails.
            Storage::disk('public')->delete($path);

            throw $exception;
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