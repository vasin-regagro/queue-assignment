<?php

namespace Database\Seeders;

use App\Enums\QueueStatus;
use App\Models\ServiceQueue;
use Illuminate\Database\Seeder;

class DefaultQueueSeeder extends Seeder
{
    public function run(): void
    {
        ServiceQueue::query()->firstOrCreate(
            ['name' => 'Default'],
            [
                'status' => QueueStatus::OPEN,
                'next_ticket_number' => 1,
            ],
        );
    }
}
