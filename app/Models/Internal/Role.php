<?php

namespace App\Models\Internal;

use Spatie\Permission\Models\Role as SpatieRole;
use App\Enums\RoleStatus;
use App\Models\Internal\AccessGroup;

class Role extends SpatieRole
{
    protected $fillable = [
        'name',
        'label',
        'guard_name',
        'status',
        'access_group_id',
    ];

    protected $casts = [
        'status' => RoleStatus::class,
    ];

    public function roleGroup()
    {
        return $this->belongsTo(AccessGroup::class, 'access_group_id');
    }
}