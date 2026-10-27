<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AccessGroupResource extends JsonResource
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
   
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}