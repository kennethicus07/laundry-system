<?php

namespace App\Modules\Dashboard\Member\Controllers;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
  public function __invoke()
{
    return inertia('Dashboard/Member/Index');
}
}