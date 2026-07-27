<?php

namespace Tests\Feature;

use App\Enums\QueueEntryStatus;
use App\Models\QueueEntry;
use App\Models\ServiceQueue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PublicQueueBoardTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_board_lists_all_queues_and_active_entries_in_fifo_order(): void
    {
        $openQueue = $this->queue('Main desk', 'OPEN');
        $closedQueue = $this->queue('Evening desk', 'CLOSED');
        $later = User::factory()->create([
            'name' => 'Later Person',
            'email' => 'later-private@example.test',
        ]);
        $earlier = User::factory()->create([
            'name' => 'Earlier Person',
            'email' => 'earlier-private@example.test',
        ]);
        $called = User::factory()->create(['name' => 'Called Person']);

        $this->entry($openQueue, $later, 2, QueueEntryStatus::WAITING, now()->subMinutes(5));
        $this->entry($openQueue, $earlier, 1, QueueEntryStatus::WAITING, now()->subMinutes(10));
        $this->entry($openQueue, $called, 3, QueueEntryStatus::CALLED, now()->subMinutes(3));

        DB::enableQueryLog();

        $response = $this->get('/queues-board')
            ->assertOk()
            ->assertHeader('content-type', 'text/html; charset=UTF-8')
            ->assertSee('Main desk')
            ->assertSee('Evening desk')
            ->assertSee('Открыта')
            ->assertSee('Закрыта')
            ->assertSee('Активных: 3')
            ->assertSee('№ 1')
            ->assertSee('Ожидает')
            ->assertSee('Вызван')
            ->assertSeeInOrder(['Earlier Person', 'Later Person', 'Called Person']);

        $this->assertLessThanOrEqual(3, count(DB::getQueryLog()));
        $response->assertDontSee('earlier-private@example.test');
        $response->assertDontSee('later-private@example.test');
        $response->assertDontSee('data-user-id');
    }

    public function test_public_board_excludes_completed_and_cancelled_entries(): void
    {
        $queue = $this->queue('Privacy desk', 'OPEN');
        $active = User::factory()->create(['name' => 'Active Person']);
        $completed = User::factory()->create(['name' => 'Completed Private Person']);
        $cancelled = User::factory()->create(['name' => 'Cancelled Private Person']);

        $this->entry($queue, $active, 1, QueueEntryStatus::SERVING, now()->subMinutes(8));
        $this->entry($queue, $completed, 2, QueueEntryStatus::COMPLETED, now()->subMinutes(7));
        $this->entry($queue, $cancelled, 3, QueueEntryStatus::CANCELLED, now()->subMinutes(6));

        $this->get('/queues-board')
            ->assertOk()
            ->assertSee('Active Person')
            ->assertSee('Обслуживается')
            ->assertSee('Активных: 1')
            ->assertDontSee('Completed Private Person')
            ->assertDontSee('Cancelled Private Person')
            ->assertDontSee('Завершён')
            ->assertDontSee('Отменён');
    }

    public function test_public_board_renders_empty_states_and_auto_refresh(): void
    {
        $this->get('/queues-board')
            ->assertOk()
            ->assertSee('Очередей пока нет')
            ->assertSee('http-equiv="refresh"', false)
            ->assertSee('content="15"', false);

        $this->queue('Empty desk', 'OPEN');

        $this->get('/queues-board')
            ->assertOk()
            ->assertSee('Empty desk')
            ->assertSee('Активных: 0')
            ->assertSee('В этой очереди пока никого нет');
    }

    private function queue(string $name, string $status): ServiceQueue
    {
        return ServiceQueue::create([
            'name' => $name,
            'status' => $status,
            'next_ticket_number' => 1,
        ]);
    }

    private function entry(
        ServiceQueue $queue,
        User $user,
        int $ticket,
        QueueEntryStatus $status,
        \DateTimeInterface $joinedAt,
    ): QueueEntry {
        return QueueEntry::create([
            'queue_id' => $queue->id,
            'user_id' => $user->id,
            'ticket_number' => $ticket,
            'status' => $status,
            'active_marker' => in_array($status, [
                QueueEntryStatus::WAITING,
                QueueEntryStatus::CALLED,
                QueueEntryStatus::SERVING,
            ], true) ? 'ACTIVE' : null,
            'serving_marker' => $status === QueueEntryStatus::SERVING ? 'SERVING' : null,
            'operator_user_id' => null,
            'joined_at' => $joinedAt,
            'called_at' => in_array($status, [
                QueueEntryStatus::CALLED,
                QueueEntryStatus::SERVING,
            ], true) ? $joinedAt->modify('+2 minutes') : null,
            'service_started_at' => $status === QueueEntryStatus::SERVING
                ? $joinedAt->modify('+4 minutes')
                : null,
            'completed_at' => $status === QueueEntryStatus::COMPLETED
                ? $joinedAt->modify('+5 minutes')
                : null,
            'cancelled_at' => $status === QueueEntryStatus::CANCELLED
                ? $joinedAt->modify('+5 minutes')
                : null,
        ]);
    }
}
