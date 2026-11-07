<?php

namespace App\Modules\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

/**
 * Historical snapshot of an invoice line item. Append-only.
 */
class InvoiceRevisionItem extends Model
{
    protected $fillable = [
        'invoice_revision_id',
        'description',
        'quantity',
        'unit_price',
        'line_total',
    ];

    protected function casts(): array
    {
        return [
            'invoice_revision_id' => 'integer',
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function () {
            throw new LogicException('Invoice revision items are historical snapshots and cannot be modified.');
        });

        static::deleting(function () {
            throw new LogicException('Invoice revision items are historical snapshots and cannot be deleted.');
        });
    }

    public function revision(): BelongsTo
    {
        return $this->belongsTo(InvoiceRevision::class, 'invoice_revision_id');
    }
}