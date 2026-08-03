<?php

namespace Tests\UseCases;

use App\Enums\QueueEntryStatus;
use App\Enums\UserRole;
use App\Models\OperatorAssignment;
use App\Models\QueueEntry;
use App\Models\ServiceQueue;
use App\Models\User;
use App\Services\JwtService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

abstract class UseCaseTestCase extends TestCase
{
    use RefreshDatabase;

    protected function asUser(User $user): static
    {
        return $this->withHeader(
            'Authorization',
            'Bearer '.app(JwtService::class)->issue($user),
        );
    }

    protected function administrator(): User
    {
        return User::factory()->create([
            'roles' => [UserRole::USER->value, UserRole::ADMINISTRATOR->value],
        ]);
    }

    protected function operator(): User
    {
        return User::factory()->create([
            'roles' => [UserRole::USER->value, UserRole::OPERATOR->value],
        ]);
    }

    protected function queue(string $status = 'OPEN', string $name = 'Acceptance queue'): ServiceQueue
    {
        return ServiceQueue::create([
            'name' => $name,
            'status' => $status,
            'next_ticket_number' => 1,
        ]);
    }

    protected function assign(User $administrator, User $operator, ServiceQueue $queue): OperatorAssignment
    {
        return OperatorAssignment::create([
            'operator_user_id' => $operator->id,
            'queue_id' => $queue->id,
            'assigned_by' => $administrator->id,
            'active_marker' => 'ACTIVE',
            'assigned_at' => now(),
        ]);
    }

    protected function entry(
        ServiceQueue $queue,
        User $user,
        int $ticket,
        QueueEntryStatus $status = QueueEntryStatus::WAITING,
        ?User $operator = null,
        mixed $joinedAt = null,
    ): QueueEntry {
        $joinedAt ??= now();

        return QueueEntry::create([
            'queue_id' => $queue->id,
            'user_id' => $user->id,
            'operator_user_id' => $operator?->id,
            'ticket_number' => $ticket,
            'status' => $status,
            'active_marker' => in_array($status, [
                QueueEntryStatus::WAITING,
                QueueEntryStatus::CALLED,
                QueueEntryStatus::SERVING,
            ], true) ? 'ACTIVE' : null,
            'serving_marker' => $status === QueueEntryStatus::SERVING ? 'SERVING' : null,
            'joined_at' => $joinedAt,
            'called_at' => in_array($status, [QueueEntryStatus::CALLED, QueueEntryStatus::SERVING], true)
                ? now()
                : null,
            'service_started_at' => $status === QueueEntryStatus::SERVING ? now() : null,
            'completed_at' => $status === QueueEntryStatus::COMPLETED ? now() : null,
            'cancelled_at' => $status === QueueEntryStatus::CANCELLED ? now() : null,
        ]);
    }
}
