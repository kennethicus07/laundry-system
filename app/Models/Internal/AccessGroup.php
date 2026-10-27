<?php

namespace App\Models\Internal;

use Illuminate\Database\Eloquent\Model;
use App\Enums\Status;

class AccessGroup extends Model
{
    protected $fillable = [
        'name',
        'label',
        'status',
    ];

        protected $casts = [
        'status' => Status::class,
    ];


    public function permissions()
    {
        return $this->hasMany(Permission::class);
    }

    public function roles()
    {
        return $this->hasMany(Role::class);
    }
}