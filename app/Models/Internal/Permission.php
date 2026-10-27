<?php

namespace App\Models\Internal;

use Spatie\Permission\Models\Permission as SpatiePermission;

use App\Enums\PermissionStatus;
use App\Models\Internal\AccessGroup;

class Permission extends SpatiePermission
{
    protected $fillable = [
        'name',
        'label',
        'guard_name',
        'status',
        'access_group_id',
        'features_id'
    ];

     protected $casts = [
        'status' => PermissionStatus::class, 
    ];

    public function accessGroup()
    {
        return $this->belongsTo(AccessGroup::class, 'access_group_id');
    }

    public function features()
    {
        return $this->belongsTo(Feature::class, 'features_id');
    }
}