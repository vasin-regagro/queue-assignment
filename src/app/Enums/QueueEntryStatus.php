<?php

namespace App\Enums;

enum QueueEntryStatus: string
{
    case WAITING = 'WAITING';
    case CALLED = 'CALLED';
    case SERVING = 'SERVING';
    case COMPLETED = 'COMPLETED';
    case CANCELLED = 'CANCELLED';

    public function isActive(): bool
    {
        return in_array($this, [self::WAITING, self::CALLED, self::SERVING], true);
    }
}
