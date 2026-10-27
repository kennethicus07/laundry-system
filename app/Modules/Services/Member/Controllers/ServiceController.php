<?php

namespace App\Modules\Services\Member\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Services\Member\Actions\GetAvailableServices;
use App\Modules\Services\Member\Actions\GetServiceDetails;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ServiceController extends Controller
{
    public function index(
        GetAvailableServices $action,
    ): Response {
        return Inertia::render('Services/Member/Index', [
            'services' => $action->execute(),
        ]);           
    }

    public function show(
        int $service,
        GetServiceDetails $action,
    ): Response {
        $serviceDetails = $action->execute($service);

        abort_if($serviceDetails === null, 404);

        return Inertia::render('Services/Member/Show', [
            'service' => $serviceDetails,
        ]);
    }
}