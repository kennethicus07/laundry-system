<?php
namespace App\Modules\RolePermission\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\RolePermission\Services\RolePermissionService;
use App\Modules\RolePermission\DTO\RolePermissionSyncData;

class RolePermissionSyncController extends Controller
{
    public function store(Request $request, RolePermissionService $service)
    {
        $dto = RolePermissionSyncData::from($request->all());

        $service->sync($dto);

        return back();
    }
}