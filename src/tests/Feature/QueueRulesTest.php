<?php

namespace Tests\Feature;

use App\Enums\QueueEntryStatus;
use App\Enums\UserRole;
use App\Models\OperatorAssignment;
use App\Models\QueueEntry;
use App\Models\ServiceQueue;
use App\Models\User;
use App\Services\JwtService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QueueRulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_administrators_can_manage_queues_and_operator_assignments(): void
    {
        $user = User::factory()->create();
        $queue = $this->queue();
        $operator = User::factory()->create([
            'roles' => [UserRole::USER->value, UserRole::OPERATOR->value],
        ]);

        $this->actingAsApi($user)
            ->withHeader('Idempotency-Key', 'create-forbidden')
            ->postJson('/api/queues', ['name' => 'Forbidden'])
            ->assertForbidden()
            ->assertJsonPath('error.code', 'FORBIDDEN');

        $this->actingAsApi($user)
            ->withHeader('Idempotency-Key', 'assign-forbidden')
            ->postJson("/api/queues/$queue->id/operators/$operator->id")
            ->assertForbidden();

        $this->assertDatabaseMissing('service_queues', ['name' => 'Forbidden']);
        $this->assertDatabaseCount('operator_assignments', 0);
    }

    public function test_mutations_require_idempotency_and_reject_reuse_with_different_parameters(): void
    {
        $admin = $this->administrator();

        $this->actingAsApi($admin)
            ->postJson('/api/queues', ['name' => 'No key'])
            ->assertUnprocessable()
            ->assertJsonPath('error.code', 'IDEMPOTENCY_KEY_REQUIRED');

        $this->actingAsApi($admin)
            ->withHeader('Idempotency-Key', 'same-key')
            ->postJson('/api/queues', ['name' => 'First'])
            ->assertCreated();

        $this->actingAsApi($admin)
            ->withHeader('Idempotency-Key', 'same-key')
            ->postJson('/api/queues', ['name' => 'Second'])
            ->assertConflict()
            ->assertJsonPath('error.code', 'IDEMPOTENCY_CONFLICT');

        $this->assertDatabaseCount('service_queues', 1);
    }

    public function test_closed_queue_and_duplicate_active_entry_are_rejected(): void
    {
        $user = User::factory()->create();
        $queue = $this->queue('CLOSED');

        $this->actingAsApi($user)
            ->withHeader('Idempotency-Key', 'closed-join')
            ->postJson("/api/queues/$queue->id/entries")
            ->assertConflict()
            ->assertJsonPath('error.code', 'QUEUE_CLOSED');

        $queue->update(['status' => 'OPEN']);

        $this->actingAsApi($user)
            ->withHeader('Idempotency-Key', 'first-join')
            ->postJson("/api/queues/$queue->id/entries")
            ->assertCreated();

        $this->actingAsApi($user)
            ->withHeader('Idempotency-Key', 'duplicate-join')
            ->postJson("/api/queues/$queue->id/entries")
            ->assertConflict()
            ->assertJsonPath('error.code', 'ACTIVE_ENTRY_EXISTS');
    }

    public function test_entry_can_only_be_cancelled_by_owner_in_waiting_or_called_state(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $queue = $this->queue();
        $entry = $this->entry($queue, $owner);

        $this->actingAsApi($other)
            ->withHeader('Idempotency-Key', 'cancel-other')
            ->postJson("/api/entries/$entry->id/cancel")
            ->assertForbidden();

        $entry->update([
            'status' => QueueEntryStatus::COMPLETED,
            'active_marker' => null,
            'completed_at' => now(),
        ]);

        $this->actingAsApi($owner)
            ->withHeader('Idempotency-Key', 'cancel-completed')
            ->postJson("/api/entries/$entry->id/cancel")
            ->assertConflict()
            ->assertJsonPath('error.code', 'INVALID_STATE_TRANSITION');
    }

    public function test_operator_must_be_assigned_and_cannot_serve_two_entries(): void
    {
        $admin = $this->administrator();
        $operator = User::factory()->create([
            'roles' => [UserRole::USER->value, UserRole::OPERATOR->value],
        ]);
        $queue = $this->queue();
        $first = $this->entry($queue, User::factory()->create(), QueueEntryStatus::CALLED, 1);
        $second = $this->entry($queue, User::factory()->create(), QueueEntryStatus::CALLED, 2);

        $this->actingAsApi($operator)
            ->withHeader('Idempotency-Key', 'start-unassigned')
            ->postJson("/api/entries/$first->id/start-service")
            ->assertForbidden();

        OperatorAssignment::create([
            'operator_user_id' => $operator->id,
            'queue_id' => $queue->id,
            'assigned_by' => $admin->id,
            'active_marker' => 'ACTIVE',
            'assigned_at' => now(),
        ]);

        $this->actingAsApi($operator)
            ->withHeader('Idempotency-Key', 'start-first')
            ->postJson("/api/entries/$first->id/start-service")
            ->assertOk();

        $this->actingAsApi($operator)
            ->withHeader('Idempotency-Key', 'start-second')
            ->postJson("/api/entries/$second->id/start-service")
            ->assertConflict()
            ->assertJsonPath('error.code', 'OPERATOR_BUSY');
    }

    public function test_only_serving_operator_can_complete_an_entry_and_empty_queue_cannot_call_next(): void
    {
        $admin = $this->administrator();
        $operator = User::factory()->create([
            'roles' => [UserRole::USER->value, UserRole::OPERATOR->value],
        ]);
        $otherOperator = User::factory()->create([
            'roles' => [UserRole::USER->value, UserRole::OPERATOR->value],
        ]);
        $queue = $this->queue();

        foreach ([$operator, $otherOperator] as $assignedOperator) {
            OperatorAssignment::create([
                'operator_user_id' => $assignedOperator->id,
                'queue_id' => $queue->id,
                'assigned_by' => $admin->id,
                'active_marker' => 'ACTIVE',
                'assigned_at' => now(),
            ]);
        }

        $this->actingAsApi($operator)
            ->withHeader('Idempotency-Key', 'call-empty')
            ->postJson("/api/queues/$queue->id/call-next")
            ->assertConflict()
            ->assertJsonPath('error.code', 'NO_WAITING_ENTRIES');

        $entry = $this->entry($queue, User::factory()->create(), QueueEntryStatus::SERVING);
        $entry->update([
            'operator_user_id' => $operator->id,
            'serving_marker' => 'SERVING',
            'service_started_at' => now(),
        ]);

        $this->actingAsApi($otherOperator)
            ->withHeader('Idempotency-Key', 'complete-other')
            ->postJson("/api/entries/$entry->id/complete-service")
            ->assertConflict()
            ->assertJsonPath('error.code', 'INVALID_STATE_TRANSITION');
    }

    private function actingAsApi(User $user): static
    {
        return $this->withHeader('Authorization', 'Bearer '.app(JwtService::class)->issue($user));
    }

    private function administrator(): User
    {
        return User::factory()->create([
            'roles' => [UserRole::USER->value, UserRole::ADMINISTRATOR->value],
        ]);
    }

    private function queue(string $status = 'OPEN'): ServiceQueue
    {
        return ServiceQueue::create([
            'name' => fake()->unique()->words(2, true),
            'status' => $status,
            'next_ticket_number' => 1,
        ]);
    }

    private function entry(
        ServiceQueue $queue,
        User $user,
        QueueEntryStatus $status = QueueEntryStatus::WAITING,
        int $ticket = 1,
    ): QueueEntry {
        return QueueEntry::create([
            'queue_id' => $queue->id,
            'user_id' => $user->id,
            'ticket_number' => $ticket,
            'status' => $status,
            'active_marker' => 'ACTIVE',
            'joined_at' => now(),
            'called_at' => $status === QueueEntryStatus::CALLED ? now() : null,
        ]);
    }
}
