<?php

namespace Tests\UseCases;

use App\Enums\QueueEntryStatus;
use App\Models\QueueEntry;
use App\Models\User;
use App\Services\JwtService;

class IdentityAndQueueUseCasesTest extends UseCaseTestCase
{
    public function test_uc_001_identity_is_issued_for_registered_and_guest_users(): void
    {
        $registered = $this->postJson('/api/auth/register', [
            'name' => 'UC 001 User',
            'email' => 'uc001@example.test',
            'password' => 'password-123',
        ])
            ->assertCreated()
            ->assertJsonPath('data.tokenType', 'Bearer')
            ->assertJsonPath('data.user.isGuest', false)
            ->assertJsonStructure(['data' => ['accessToken']]);

        $claims = app(JwtService::class)->decode($registered->json('data.accessToken'));
        $this->assertSame('uc001@example.test', User::findOrFail($claims['sub'])->email);

        $this->postJson('/api/auth/guest', ['name' => 'UC 001 Guest'])
            ->assertCreated()
            ->assertJsonPath('data.expiresIn', 86400)
            ->assertJsonPath('data.user.isGuest', true);

        $this->postJson('/api/auth/register', [
            'name' => 'Duplicate',
            'email' => 'uc001@example.test',
            'password' => 'password-123',
        ])->assertUnprocessable();
    }

    public function test_uc_002_administrator_changes_queue_state_idempotently(): void
    {
        $administrator = $this->administrator();
        $ordinaryUser = User::factory()->create();

        $created = $this->asUser($administrator)
            ->withHeader('Idempotency-Key', 'uc002-create')
            ->postJson('/api/queues', ['name' => 'UC 002 Queue'])
            ->assertCreated()
            ->assertJsonPath('data.status', 'CLOSED');
        $queueId = $created->json('data.id');

        $this->asUser($administrator)
            ->withHeader('Idempotency-Key', 'uc002-open')
            ->postJson("/api/queues/$queueId/open")
            ->assertOk()
            ->assertJsonPath('data.status', 'OPEN');

        $this->asUser($administrator)
            ->withHeader('Idempotency-Key', 'uc002-close')
            ->postJson("/api/queues/$queueId/close")
            ->assertOk()
            ->assertJsonPath('data.status', 'CLOSED');

        $this->asUser($ordinaryUser)
            ->withHeader('Idempotency-Key', 'uc002-forbidden')
            ->postJson("/api/queues/$queueId/open")
            ->assertForbidden();
    }

    public function test_uc_003_administrator_assigns_and_unassigns_an_operator(): void
    {
        $administrator = $this->administrator();
        $operator = $this->operator();
        $queue = $this->queue();

        $this->asUser($administrator)
            ->withHeader('Idempotency-Key', 'uc003-assign')
            ->postJson("/api/queues/$queue->id/operators/$operator->id")
            ->assertCreated()
            ->assertJsonPath('data.active', true);

        $this->assertDatabaseHas('operator_assignments', [
            'queue_id' => $queue->id,
            'operator_user_id' => $operator->id,
            'active_marker' => 'ACTIVE',
        ]);

        $this->asUser($administrator)
            ->withHeader('Idempotency-Key', 'uc003-unassign')
            ->deleteJson("/api/queues/$queue->id/operators/$operator->id")
            ->assertOk()
            ->assertJsonPath('data.active', false);

        $this->assertDatabaseMissing('operator_assignments', [
            'queue_id' => $queue->id,
            'operator_user_id' => $operator->id,
            'active_marker' => 'ACTIVE',
        ]);
    }

    public function test_uc_004_user_joins_an_open_queue_once_and_receives_a_ticket(): void
    {
        $user = User::factory()->create();
        $queue = $this->queue();

        $response = $this->asUser($user)
            ->withHeader('Idempotency-Key', 'uc004-join')
            ->postJson("/api/queues/$queue->id/entries")
            ->assertCreated()
            ->assertJsonPath('data.ticketNumber', 1)
            ->assertJsonPath('data.status', 'WAITING')
            ->assertJsonPath('data.position', 1);

        $this->assertDatabaseHas('queue_entries', [
            'id' => $response->json('data.id'),
            'queue_id' => $queue->id,
            'user_id' => $user->id,
            'active_marker' => 'ACTIVE',
        ]);

        $this->asUser($user)
            ->withHeader('Idempotency-Key', 'uc004-second-attempt')
            ->postJson("/api/queues/$queue->id/entries")
            ->assertConflict()
            ->assertJsonPath('error.code', 'ACTIVE_ENTRY_EXISTS');
    }

    public function test_uc_005_user_receives_current_fifo_position_for_own_entry(): void
    {
        $queue = $this->queue();
        $firstUser = User::factory()->create();
        $secondUser = User::factory()->create();
        $joinedAt = now();
        $this->entry($queue, $firstUser, 1, QueueEntryStatus::WAITING, joinedAt: $joinedAt);
        $secondEntry = $this->entry($queue, $secondUser, 2, QueueEntryStatus::WAITING, joinedAt: $joinedAt);

        $this->asUser($secondUser)
            ->getJson("/api/queues/$queue->id/position")
            ->assertOk()
            ->assertJsonPath('data.id', $secondEntry->id)
            ->assertJsonPath('data.ticketNumber', 2)
            ->assertJsonPath('data.position', 2);

        $stranger = User::factory()->create();
        $this->asUser($stranger)
            ->getJson("/api/queues/$queue->id/position")
            ->assertNotFound();
    }

    public function test_uc_006_owner_cancels_waiting_or_called_entry_without_partial_effects(): void
    {
        $queue = $this->queue();
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $entry = $this->entry($queue, $owner, 1, QueueEntryStatus::CALLED);

        $this->asUser($stranger)
            ->withHeader('Idempotency-Key', 'uc006-stranger')
            ->postJson("/api/entries/$entry->id/cancel")
            ->assertForbidden();
        $this->assertSame(QueueEntryStatus::CALLED, $entry->fresh()->status);

        $this->asUser($owner)
            ->withHeader('Idempotency-Key', 'uc006-owner')
            ->postJson("/api/entries/$entry->id/cancel")
            ->assertOk()
            ->assertJsonPath('data.status', 'CANCELLED');

        $cancelled = QueueEntry::findOrFail($entry->id);
        $this->assertNull($cancelled->active_marker);
        $this->assertNotNull($cancelled->cancelled_at);
    }
}
