<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;



class UserResource extends JsonResource{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,

            'roles' => $this->roles->map(fn ($r) => [
                'id' => $r->id,
                'label' => $r->label,
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