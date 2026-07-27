<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\QueueEntry;
use App\Models\User;
use App\Services\JwtService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QueueWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_queue_workflow_and_idempotent_replay(): void
    {
        $admin = User::factory()->create(['roles' => [UserRole::USER->value, UserRole::ADMINISTRATOR->value]]);
        $operator = User::factory()->create(['roles' => [UserRole::USER->value, UserRole::OPERATOR->value]]);
        $customer = User::factory()->create();
        $secondCustomer = User::factory()->create();

        $create = $this->withTokenFor($admin)
            ->withHeader('Idempotency-Key', 'create-queue-1')
            ->postJson('/api/queues', ['name' => 'Main desk'])
            ->assertCreated()
            ->assertJsonPath('data.status', 'CLOSED');
        $queueId = $create->json('data.id');

        $this->withTokenFor($admin)
            ->withHeader('Idempotency-Key', 'open-queue-1')
            ->postJson("/api/queues/$queueId/open")
            ->assertOk()
            ->assertJsonPath('data.status', 'OPEN');

        $this->withTokenFor($admin)
            ->withHeader('Idempotency-Key', 'assign-operator-1')
            ->postJson("/api/queues/$queueId/operators/$operator->id")
            ->assertCreated()
            ->assertJsonPath('data.active', true);

        $join = $this->withTokenFor($customer)
            ->withHeader('Idempotency-Key', 'join-customer-1')
            ->postJson("/api/queues/$queueId/entries")
            ->assertCreated()
            ->assertJsonPath('data.ticketNumber', 1)
            ->assertJsonPath('data.position', 1);
        $firstEntryId = $join->json('data.id');

        $replay = $this->withTokenFor($customer)
            ->withHeader('Idempotency-Key', 'join-customer-1')
            ->postJson("/api/queues/$queueId/entries")
            ->assertCreated();
        $this->assertSame($firstEntryId, $replay->json('data.id'));

        $secondJoin = $this->withTokenFor($secondCustomer)
            ->withHeader('Idempotency-Key', 'join-customer-2')
            ->postJson("/api/queues/$queueId/entries")
            ->assertCreated()
            ->assertJsonPath('data.ticketNumber', 2)
            ->assertJsonPath('data.position', 2);
        $secondEntryId = $secondJoin->json('data.id');

        $this->withTokenFor($operator)
            ->withHeader('Idempotency-Key', 'call-next-1')
            ->postJson("/api/queues/$queueId/call-next")
            ->assertOk()
            ->assertJsonPath('data.id', $firstEntryId)
            ->assertJsonPath('data.status', 'CALLED');

        $this->withTokenFor($customer)
            ->getJson("/api/queues/$queueId/position")
            ->assertOk()
            ->assertJsonPath('data.position', null)
            ->assertJsonPath('data.status', 'CALLED');

        $this->withTokenFor($operator)
            ->withHeader('Idempotency-Key', 'start-service-1')
            ->postJson("/api/entries/$firstEntryId/start-service")
            ->assertOk()
            ->assertJsonPath('data.status', 'SERVING');

        $this->withTokenFor($operator)
            ->withHeader('Idempotency-Key', 'complete-service-1')
            ->postJson("/api/entries/$firstEntryId/complete-service")
            ->assertOk()
            ->assertJsonPath('data.status', 'COMPLETED');

        $this->withTokenFor($operator)
            ->withHeader('Idempotency-Key', 'call-next-2')
            ->postJson("/api/queues/$queueId/call-next")
            ->assertOk()
            ->assertJsonPath('data.id', $secondEntryId);

        $this->withTokenFor($secondCustomer)
            ->withHeader('Idempotency-Key', 'cancel-called-2')
            ->postJson("/api/entries/$secondEntryId/cancel")
            ->assertOk()
            ->assertJsonPath('data.status', 'CANCELLED');

        $this->assertSame(2, QueueEntry::count());
    }

    private function withTokenFor(User $user): static
    {
        return $this->withHeader('Authorization', 'Bearer '.app(JwtService::class)->issue($user));
    }
}
