<?php

namespace App\Modules\Orders\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\User;
use App\Modules\Booking\Models\Booking;
use App\Modules\Scheduling\Models\Schedule;
use App\Modules\Billing\Models\Invoice;

class LaundryOrder extends Model
{
    public const STATUS_AWAITING_PICKUP = 'awaiting_pickup';
    public const STATUS_RECEIVED = 'received';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_READY_FOR_DELIVERY = 'ready_for_delivery';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $table = 'laundry_orders';

    protected $fillable = [
        'order_number',
        'booking_id',
        'customer_id',
        'status',
        'received_at',
        'processing_started_at',
        'ready_at',
        'completed_at',
        'cancelled_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'received_at' => 'datetime',
            'processing_started_at' => 'datetime',
            'ready_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(LaundryOrderItem::class, 'order_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'order_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'order_id');
    }
}
