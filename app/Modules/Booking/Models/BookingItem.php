<?php

namespace App\Modules\Booking\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingItem extends Model
{
    protected $fillable = [
        'booking_id',
        'service_id',
        'service_name',
        'pricing_unit',
        'estimated_quantity',
        'estimated_unit_price',
        'estimated_subtotal',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'estimated_quantity' => 'decimal:2',
            'estimated_unit_price' => 'decimal:2',
            'estimated_subtotal' => 'decimal:2',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
