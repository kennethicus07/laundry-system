<?php

namespace App\Modules\Billing\Models;

use App\Models\User;
use App\Modules\Billing\Enums\PaymentMethod;
use App\Modules\Billing\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    /**
     * Private disk for QR screenshots. In Laravel 11+, the "local" disk
     * points to storage/app/private and is not publicly accessible.
     */
    public const PROOF_DISK = 'local';
    public const PROOF_DIRECTORY = 'billing/payment-proofs';

    /** Upload limits for QR proof screenshots (used in form requests). */
    public const PROOF_MAX_KB = 5120;
    public const PROOF_MIMES = ['jpg', 'jpeg', 'png', 'webp'];

    /** Statuses that block reuse of the same QR reference number. */
    public const REFERENCE_LOCKING_STATUSES = [
        PaymentStatus::PendingVerification->value,
        PaymentStatus::Completed->value,
    ];

    protected $fillable = [
        'invoice_id',
        'amount',
        'method',
        'status',
        'reference_number',
        'proof_path',
        'idempotency_key',
        'recorded_by',
        'verified_by',
        'received_at',
        'paid_at',
        'failed_at',
        'notes',
    ];

    protected $attributes = [
        'status' => PaymentStatus::PendingVerification->value,
    ];

    // Never leak the private storage path in JSON / Inertia props.
    protected $hidden = [
        'proof_path',
    ];

    protected function casts(): array
    {
        return [
            'invoice_id' => 'integer',
            'amount' => 'decimal:2',
            'method' => PaymentMethod::class,
            'status' => PaymentStatus::class,
            'recorded_by' => 'integer',
            'verified_by' => 'integer',
            'received_at' => 'datetime',
            'paid_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', PaymentStatus::Completed->value);
    }

    public function scopePendingVerification(Builder $query): Builder
    {
        return $query->where('status', PaymentStatus::PendingVerification->value);
    }

    public function scopeQr(Builder $query): Builder
    {
        return $query->where('method', PaymentMethod::Qr->value);
    }

    public function scopeWithLockedReference(Builder $query, string $reference): Builder
    {
        return $query->qr()
            ->where('reference_number', $reference)
            ->whereIn('status', self::REFERENCE_LOCKING_STATUSES);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isPending(): bool
    {
        return $this->status === PaymentStatus::PendingVerification;
    }

    public function isCompleted(): bool
    {
        return $this->status === PaymentStatus::Completed;
    }

    public function isQr(): bool
    {
        return $this->method === PaymentMethod::Qr;
    }

    public function hasProof(): bool
    {
        return filled($this->proof_path);
    }
}