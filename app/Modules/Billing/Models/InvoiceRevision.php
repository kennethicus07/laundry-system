<?php

namespace App\Modules\Billing\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LogicException;

/**
 * Historical snapshot of an invoice header before it was revised.
 * Records are append-only: they can be created but never updated or deleted
 * through Eloquent.
 */
class InvoiceRevision extends Model
{
    protected $fillable = [
        'invoice_id',
        'revision_number',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'reason',
        'revised_by',
        'revised_at',
    ];

    protected function casts(): array
    {
        return [
            'invoice_id' => 'integer',
            'revision_number' => 'integer',
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'revised_by' => 'integer',
            'revised_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function () {
            throw new LogicException('Invoice revisions are historical snapshots and cannot be modified.');
        });

        static::deleting(function () {
            throw new LogicException('Invoice revisions are historical snapshots and cannot be deleted.');
        });
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceRevisionItem::class);
    }

    public function revisedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revised_by');
    }
}