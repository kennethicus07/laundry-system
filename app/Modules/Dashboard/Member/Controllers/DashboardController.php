<?php

namespace App\Modules\Dashboard\Member\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Dashboard\Member\Services\GetMemberDashboard;
use Illuminate\Http\Request;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(
        Request $request,
        GetMemberDashboard $dashboard,
    ): Response {
        $user = $request->user();

        return inertia('Dashboard/Member/Index', [
            'customerName' => $user->name,
            'dashboard' => $dashboard->handle(
                (int) $user->id,
            ),
        ]);
    }
}