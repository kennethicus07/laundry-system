<?php

namespace App\Modules\AccessGroup\Services;

use App\Models\Internal\AccessGroup;
use App\Modules\AccessGroup\DTO\AccessGroupData;
use App\Enums\Status;

class AccessGroupService
{
public function create(AccessGroupData $data): AccessGroup
{
    return AccessGroup::create([
        'name' => $data->name,
        'status' => Status::ACTIVE,
    ]);
}

public function update(AccessGroup $access_group, AccessGroupData $data): AccessGroup
{
    $access_group->update([
        'name' => $data->name,
        'status' => $data->status,
    ]);

    return $access_group;
}
    public function delete(AccessGroup $access_group): bool
    {
        return $access_group->delete();
    }
}