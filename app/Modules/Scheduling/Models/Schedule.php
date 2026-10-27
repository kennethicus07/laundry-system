<?php

namespace App\Modules\Scheduling\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use App\Modules\Orders\Models\LaundryOrder;

class Schedule extends Model
{
    public const TYPE_PICKUP = 'pickup';
    public const TYPE_DELIVERY = 'delivery';

    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_MISSED = 'missed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'order_id',
        'assigned_staff_id',
        'type',
        'status',
        'scheduled_at',
        'started_at',
        'completed_at',
        'outcome_notes',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(LaundryOrder::class, 'order_id');
    }

    public function assignedStaff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_staff_id');
    }
}
