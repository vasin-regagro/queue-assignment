<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperatorAssignment extends Model
{
    protected $fillable = [
        'operator_user_id',
        'queue_id',
        'assigned_by',
        'active_marker',
        'assigned_at',
        'revoked_at',
    ];

    protected function casts(): array
    {
        return ['assigned_at' => 'datetime', 'revoked_at' => 'datetime'];
    }

    public function queue(): BelongsTo
    {
        return $this->belongsTo(ServiceQueue::class, 'queue_id');
    }
}
