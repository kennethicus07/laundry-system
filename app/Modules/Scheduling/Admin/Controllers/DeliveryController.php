<?php

namespace App\Modules\Scheduling\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Scheduling\Admin\Actions\CompleteDelivery;
use App\Modules\Scheduling\Admin\Actions\ListMyDeliveries;
use App\Modules\Scheduling\Admin\Actions\MarkDeliveryMissed;
use App\Modules\Scheduling\Admin\Actions\StartDelivery;
use App\Modules\Scheduling\Models\Schedule;
use DomainException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class DeliveryController extends Controller
{
    public function index(
        Request $request,
        ListMyDeliveries $action,
    ): Response {
        return Inertia::render('Deliveries/Admin/Index', [
            'deliveries' => $action->execute(
                (int) $request->user()->id
            ),
        ]);
    }

    public function start(
        Request $request,
        int $schedule,
        StartDelivery $action,
    ): RedirectResponse {
        try {
            $action->execute(
                $schedule,
                (int) $request->user()->id,
            );
        } catch (DomainException $exception) {
            return back()->withErrors([
                'delivery' => $exception->getMessage(),
            ]);
        }

        return back()->with(
            'success',
            'Delivery started successfully.',
        );
    }

    public function complete(
        Request $request,
        Schedule $schedule,
        CompleteDelivery $action,
    ) {
        $validated = $request->validate([
            'photo' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ]);

        $path = $request->file('photo')->store(
            'delivery-proofs',
            'public'
        );

        try {
            $action->execute(
                (int) $schedule->id,
                (int) $request->user()->id,
                $path,
            );
        } catch (\Throwable $exception) {
            Storage::disk('public')->delete($path);

            throw $exception;
        }

        return back()->with(
            'success',
            'Delivery completed successfully.'
        );
    }

    public function missed(
        Request $request,
        int $schedule,
        MarkDeliveryMissed $action,
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
                $validated['outcome_notes'] ?? null,
            );
        } catch (DomainException $exception) {
            return back()->withErrors([
                'delivery' => $exception->getMessage(),
            ]);
        }

        return back()->with(
            'success',
            'Delivery marked as missed.',
        );
    }
}