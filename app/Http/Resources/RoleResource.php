<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'label' => $this->label,
            'status' => [
                    'value' => $this->status->value,
                    'label' => $this->status->label(),
                    'key' => $this->status->name, 
                ],
         'access_group_id' => (int) $this->access_group_id,

        'permissions' => $this->whenLoaded('permissions', function () {
                return $this->permissions->map(function ($permission) {
                    return [
                        'id' => $permission->id,
                        'name' => $permission->name,
                        'label' => $permission->label,
                        'features_id' => (int) $permission->features_id,
                        'access_group_id' => (int) $permission->access_group_id,
                    ];
                });
            }),
            'role_group' => $this->whenLoaded('roleGroup', fn () => [
                'id' => $this->roleGroup->id,
                'name' => $this->roleGroup->name,
            ]),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}