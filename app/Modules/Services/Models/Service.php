<?php

namespace App\Modules\Services\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Modules\Orders\Models\LaundryOrderItem;

class Service extends Model
{
    protected $fillable = [
        'code',
        'image_path',
        'name',
        'description',
        'pricing_unit',
        'default_unit_price',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'default_unit_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(LaundryOrderItem::class, 'service_id');
    }
}
