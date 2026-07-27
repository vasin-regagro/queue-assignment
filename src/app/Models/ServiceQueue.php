<?php

namespace App\Models;

use App\Enums\QueueStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceQueue extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'status', 'next_ticket_number'];

    protected function casts(): array
    {
        return ['status' => QueueStatus::class];
    }

    public function entries(): HasMany
    {
        return $this->hasMany(QueueEntry::class, 'queue_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(OperatorAssignment::class, 'queue_id');
    }
}
