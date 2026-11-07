<?php

namespace App\Modules\Billing\Models;

use App\Models\User;
use App\Modules\Billing\Enums\RefundStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Refund extends Model
{
    /**
     * Refunds in these statuses reserve part of the payment so two
     * simultaneous requests cannot exceed the original amount.
     */
    public const RESERVING_STATUSES = [
        RefundStatus::Requested->value,
        RefundStatus::Approved->value,
        RefundStatus::Processing->value,
    ];

    /** Statuses a manager may still approve or reject. */
    public const DECIDABLE_STATUSES = [
        RefundStatus::Requested->value,
    ];

    protected $fillable = [
        'payment_id',
        'amount',
        'status',
        'reason',
        'requested_by',
        'approved_by',
        'provider_reference',
        'requested_at',
        'processed_at',
    ];

    protected $attributes = [
        'status' => RefundStatus::Requested->value,
    ];

    protected function casts(): array
    {
        return [
            'payment_id' => 'integer',
            'amount' => 'decimal:2',
            'status' => RefundStatus::class,
            'requested_by' => 'integer',
            'approved_by' => 'integer',
            'requested_at' => 'datetime',
            'processed_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', RefundStatus::Completed->value);
    }

    public function scopeReserving(Builder $query): Builder
    {
        return $query->whereIn('status', self::RESERVING_STATUSES);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isAwaitingDecision(): bool
    {
        return in_array($this->status->value, self::DECIDABLE_STATUSES, true);
    }

    public function isCompleted(): bool
    {
        return $this->status === RefundStatus::Completed;
    }
}