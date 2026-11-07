<?php

namespace App\Modules\Dashboard\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Dashboard\Admin\Services\GetAdminDashboard;

class DashboardController extends Controller
{
    public function __invoke(GetAdminDashboard $dashboard)
    {
        return inertia('Dashboard/Admin/Index', [
            'dashboard' => $dashboard->handle(),
        ]);
    }
}