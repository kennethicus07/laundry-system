<?php

namespace App\Modules\Dashboard\Admin\Controllers;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
  public function __invoke()
{
    return inertia('Dashboard/Admin/Index');
}
}