<?php

namespace App\Modules\Orders\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Services\Models\Service;

class LaundryOrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'service_id',
        'service_name',
        'pricing_unit',
        'quantity',
        'unit_price',
        'subtotal',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(LaundryOrder::class, 'order_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }
}
