<?php

namespace App\Models\Internal;

use Illuminate\Database\Eloquent\Model;
use App\Enums\Status;
use App\Models\Internal\AccessGroup;

class Feature extends Model
{
        protected $fillable = [
        'name',
        'access_group_id',
        'status',
    ];
protected $casts = [
        'status' => Status::class,
    ];

    public function accessGroup()
    {
        return $this->belongsTo(AccessGroup::class, 'access_group_id');
    }

}
