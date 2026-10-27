<?php

namespace App\Modules\AccessGroup\Controllers;

use App\Http\Resources\AccessGroupResource;
use App\Http\Controllers\Controller;
use App\Models\Internal\Feature;
use App\Models\Internal\AccessGroup;
use App\Modules\AccessGroup\Services\AccessGroupService;
use App\Modules\AccessGroup\Requests\StoreAccessGroupRequest;
use App\Modules\AccessGroup\Requests\UpdateAccessGroupRequest;
use App\Modules\AccessGroup\DTO\AccessGroupData;

class AccessGroupController extends Controller
{
    public function __construct(
        protected AccessGroupService $service
    ) {}


    public function index()
    {
        return inertia('AccessGroups/Index', [
            'access_groups' => AccessGroupResource::collection(
                AccessGroup::latest()->get()
            )->resolve(),
        ]);
    }


    public function edit(AccessGroup $accessGroup)
    {
        $accessGroup->load([
            'permissions.features',
            'roles.permissions', 
        ]);

        return inertia('AccessGroups/Edit', [
            'access_group' => (new AccessGroupResource($accessGroup))->resolve(),

            'permissions' => $accessGroup->permissions,

            'roles' => $accessGroup->roles,

            'features' => Feature::where('access_group_id', $accessGroup->id)->get(),
        ]);
    }


    public function store(StoreAccessGroupRequest $request)
    {
        $dto = AccessGroupData::fromArray($request->validated());

        $this->service->create($dto);

        return back();
    }


    public function update(UpdateAccessGroupRequest $request, AccessGroup $accessGroup)
    {
        $dto = AccessGroupData::fromArray($request->validated());

        $this->service->update($accessGroup, $dto);

        return back();
    }


    public function destroy(AccessGroup $accessGroup)
    {
        $this->service->delete($accessGroup);

        return back();
    }
}