<?php

namespace App\Modules\Billing\Models;

use App\Modules\Billing\Enums\InvoiceStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Invoice extends Model
{
    public const NUMBER_PREFIX = 'INV';
    public const INITIAL_REVISION_NUMBER = 1;

    protected $fillable = [
        'invoice_number',
        'order_id',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'status',
        'revision_number',
        'issued_at',
        'voided_at',
    ];

    // Mirrors the migration defaults so new (unsaved) models are consistent.
    protected $attributes = [
        'discount_amount' => '0.00',
        'tax_amount' => '0.00',
        'status' => InvoiceStatus::Unpaid->value,
        'revision_number' => self::INITIAL_REVISION_NUMBER,
    ];

    protected function casts(): array
    {
        return [
            'order_id' => 'integer',
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'revision_number' => 'integer',
            'status' => InvoiceStatus::class,
            'issued_at' => 'datetime',
            'voided_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    | Note: there is intentionally no order() relationship. Billing reads
    | order data only through the BillableOrderReader contract.
    */

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class); 
    }

    public function refunds(): HasManyThrough
    {
        return $this->hasManyThrough(Refund::class, Payment::class);
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(InvoiceRevision::class)
            ->orderByDesc('revision_number');
    }

    public function latestRevision(): HasOne
    {
        return $this->hasOne(InvoiceRevision::class)
            ->ofMany('revision_number', 'max');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', '!=', InvoiceStatus::Void->value);
    }

    public function scopeForOrder(Builder $query, int $orderId): Builder
    {
        return $query->where('order_id', $orderId);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public static function generateNumber(): string
    {
        return self::NUMBER_PREFIX.'-'.now()->format('Ymd').'-'.Str::upper(Str::random(8));
    }

    public function isVoid(): bool
    {
        return $this->status === InvoiceStatus::Void;
    }

    public function isPaid(): bool
    {
        return $this->status === InvoiceStatus::Paid;
    }
}