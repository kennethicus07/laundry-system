<?php

namespace App\Modules\Permission\Controllers;

use App\Http\Resources\PermissionResource;
use App\Http\Controllers\Controller;
use App\Models\Internal\Permission;
use App\Models\Internal\AccessGroup;
use App\Models\Internal\Feature;
use App\Modules\Permission\Services\PermissionService;
use App\Modules\Permission\Requests\StorePermissionRequest;
use App\Modules\Permission\Requests\UpdatePermissionRequest;
use App\Modules\Permission\DTO\PermissionData;

class PermissionController extends Controller
{
    public function __construct(
        protected PermissionService $service
    ) {}

public function index()
{
    return inertia('Permissions/Index', [
        'permissions' => PermissionResource::collection(
            Permission::with(['accessGroup', 'features'])
                ->latest()
                ->get()
        )->resolve(),

        'accessGroups' => AccessGroup::orderBy('name')->get(),
        'features' => Feature::orderBy('name')->get(),
    ]);
}


    public function store(StorePermissionRequest $request)
    {
        $dto = PermissionData::fromArray($request->validated());

        $this->service->create($dto);

        return back();
    }

    public function update(UpdatePermissionRequest $request, Permission $permission)
    {
        $dto = PermissionData::fromArray($request->validated());

        $this->service->update($permission, $dto);

        return back();
    }

    public function destroy(Permission $permission)
    {
        $this->service->delete($permission);

        return back();
    }
}