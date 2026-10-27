<?php

namespace App\Modules\Reliability\Models;

use Illuminate\Database\Eloquent\Model;

class OutboxMessage extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'event_id',
        'event_type',
        'aggregate_type',
        'aggregate_id',
        'payload',
        'status',
        'attempts',
        'available_at',
        'published_at',
        'last_error',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'attempts' => 'integer',
            'available_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }
}
