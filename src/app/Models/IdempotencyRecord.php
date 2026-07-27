<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IdempotencyRecord extends Model
{
    protected $fillable = [
        'user_id',
        'operation',
        'idempotency_key',
        'request_fingerprint',
        'status',
        'response_snapshot',
        'expires_at',
    ];

    protected function casts(): array
    {
        return ['response_snapshot' => 'array', 'expires_at' => 'datetime'];
    }
}
