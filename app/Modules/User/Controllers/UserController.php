<?php

namespace App\Modules\User\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Internal\Role;
use App\Models\Internal\Feature;
use App\Models\Internal\AccessGroup;
use App\Models\Internal\Permission;
use Inertia\Inertia;

use App\Modules\User\Services\UserService;
use App\Modules\User\DTO\UserData;



use App\Http\Resources\UserResource;
use App\Http\Resources\RoleResource;
use App\Http\Resources\FeatureResource;
use App\Http\Resources\AccessGroupResource;
use App\Http\Resources\PermissionResource;

use App\Modules\User\Requests\StoreUserRequest;
use App\Modules\User\Requests\UpdateUserRequest;

use App\Modules\User\Services\UserPermissionService;





class UserController extends Controller
{
    public function index()
    {
         return inertia('Users/Index', [
        'users' => UserResource::collection(
            User::with('roles')->get()
        )->resolve(),
        'auth_user' => request()->user(),
        'roles' => RoleResource::collection(Role::all())->resolve(),
    ]);
    }



    public function create()
    {
        return Inertia::render('Users/Create', [
            'roles' => Role::all(),
        ]);
    }

  public function store(StoreUserRequest $request, UserService $service)
{
    $dto = UserData::from($request->validated());

    $service->create($dto);

    return redirect()->route('users.index');
}


public function edit(User $user)
{
    $user->load('roles.roleGroup');

    return Inertia::render('Users/Edit', [
        'user' => $user,

        'roles' => RoleResource::collection(
            Role::with(['roleGroup', 'permissions'])->get()
        )->toArray(request()),

        'features' => FeatureResource::collection(
            Feature::with('accessGroup')->get()
        )->toArray(request()),

        'access_groups' => AccessGroupResource::collection(
            AccessGroup::all()
        )->toArray(request()),

        'permissions' => PermissionResource::collection(
            Permission::with(['features', 'accessGroup'])->get()
        )->toArray(request()),

        'effectivePermissions' => UserPermissionService::resolve($user),

  
        'overrides' => UserPermissionService::getOverrides($user),

        'modelPermissions' => $user->getDirectPermissions()->pluck('id')->toArray(),
        'rolePermissions' => $user->getPermissionsViaRoles()->pluck('id')->toArray(),
    ]);
}
public function update(User $user, UpdateUserRequest $request, UserService $service)
{
    $dto = UserData::from($request->validated());

    $service->update($user, $dto);

 return back()->with('success', 'User updated successfully');
}

    public function destroy(User $user, UserService $service)
    {
        $service->delete($user);

        return back();
    }
}