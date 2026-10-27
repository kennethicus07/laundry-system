<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPermissionOverride extends Model
{
    protected $table = 'user_permission_overrides';

    protected $fillable = [
        'model_id',
        'model_type',
        'permission_id',
        'effect', // 1 = allow, -1 = deny
    ];

    protected $casts = [
        'model_id' => 'integer',
        'permission_id' => 'integer',
        'effect' => 'integer',
    ];
}