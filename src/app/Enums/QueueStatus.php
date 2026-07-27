<?php

namespace App\Enums;

enum QueueStatus: string
{
    case OPEN = 'OPEN';
    case CLOSED = 'CLOSED';
}
