<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PermissionResource extends JsonResource
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
            'features_id' => (int) $this->features_id,
            'features' => $this->whenLoaded('features', fn () =>  [
                'id' => $this->features->id,
                'name' => $this->features->name,
            ]),
            'access_group' => $this->whenLoaded('accessGroup', fn () => [
                'id' => $this->accessGroup->id,
                'name' => $this->accessGroup->name,
            ]),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}