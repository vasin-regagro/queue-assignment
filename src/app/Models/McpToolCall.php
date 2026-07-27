<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class McpToolCall extends Model
{
    protected $fillable = [
        'correlation_id',
        'tool_name',
        'user_id',
        'ai_agent_id',
        'idempotency_key',
        'request_payload',
        'response_payload',
        'status',
        'error_code',
        'started_at',
        'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'request_payload' => 'array',
            'response_payload' => 'array',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }
}
