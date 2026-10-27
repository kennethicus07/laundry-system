<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FeatureResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,

            'access_group_id' => (int) $this->access_group_id,

            'access_group' => $this->whenLoaded('accessGroup', fn () => [
                'id' => $this->accessGroup->id,
                'name' => $this->accessGroup->name,
                'label' => $this->accessGroup->label,
            ]),

            'status' => [
                'value' => $this->status->value,
                'label' => $this->status->label(),
                'key' => $this->status->name,
            ],

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}