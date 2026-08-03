<?php

namespace Tests\UseCases;

use App\Enums\QueueEntryStatus;
use App\Models\IdempotencyRecord;
use App\Models\McpToolCall;
use App\Models\User;

class ServiceAndPlatformUseCasesTest extends UseCaseTestCase
{
    public function test_uc_007_assigned_operator_calls_the_next_user_in_fifo_order(): void
    {
        $administrator = $this->administrator();
        $operator = $this->operator();
        $queue = $this->queue();
        $this->assign($administrator, $operator, $queue);
        $joinedAt = now();
        $first = $this->entry($queue, User::factory()->create(), 1, joinedAt: $joinedAt);
        $second = $this->entry($queue, User::factory()->create(), 2, joinedAt: $joinedAt);

        $this->asUser($operator)
            ->withHeader('Idempotency-Key', 'uc007-call-next')
            ->postJson("/api/queues/$queue->id/call-next")
            ->assertOk()
            ->assertJsonPath('data.id', $first->id)
            ->assertJsonPath('data.status', 'CALLED');

        $this->assertSame(QueueEntryStatus::CALLED, $first->fresh()->status);
        $this->assertSame(QueueEntryStatus::WAITING, $second->fresh()->status);
    }

    public function test_uc_008_operator_starts_one_called_entry_and_cannot_serve_two(): void
    {
        $administrator = $this->administrator();
        $operator = $this->operator();
        $queue = $this->queue();
        $this->assign($administrator, $operator, $queue);
        $first = $this->entry($queue, User::factory()->create(), 1, QueueEntryStatus::CALLED);
        $second = $this->entry($queue, User::factory()->create(), 2, QueueEntryStatus::CALLED);

        $this->asUser($operator)
            ->withHeader('Idempotency-Key', 'uc008-start-first')
            ->postJson("/api/entries/$first->id/start-service")
            ->assertOk()
            ->assertJsonPath('data.status', 'SERVING');

        $this->asUser($operator)
            ->withHeader('Idempotency-Key', 'uc008-start-second')
            ->postJson("/api/entries/$second->id/start-service")
            ->assertConflict()
            ->assertJsonPath('error.code', 'OPERATOR_BUSY');

        $this->assertSame('SERVING', $first->fresh()->serving_marker);
        $this->assertSame(QueueEntryStatus::CALLED, $second->fresh()->status);
    }

    public function test_uc_009_only_the_serving_operator_completes_service(): void
    {
        $administrator = $this->administrator();
        $operator = $this->operator();
        $otherOperator = $this->operator();
        $queue = $this->queue();
        $this->assign($administrator, $operator, $queue);
        $this->assign($administrator, $otherOperator, $queue);
        $entry = $this->entry(
            $queue,
            User::factory()->create(),
            1,
            QueueEntryStatus::SERVING,
            $operator,
        );

        $this->asUser($otherOperator)
            ->withHeader('Idempotency-Key', 'uc009-wrong-operator')
            ->postJson("/api/entries/$entry->id/complete-service")
            ->assertConflict()
            ->assertJsonPath('error.code', 'INVALID_STATE_TRANSITION');

        $this->asUser($operator)
            ->withHeader('Idempotency-Key', 'uc009-complete')
            ->postJson("/api/entries/$entry->id/complete-service")
            ->assertOk()
            ->assertJsonPath('data.status', 'COMPLETED');

        $completed = $entry->fresh();
        $this->assertNull($completed->active_marker);
        $this->assertNull($completed->serving_marker);
        $this->assertNotNull($completed->completed_at);
    }

    public function test_uc_010_mcp_call_is_authenticated_audited_and_sanitized(): void
    {
        $user = User::factory()->create();
        $this->queue();

        $this->asUser($user)
            ->withHeader('MCP-Agent-ID', 'uc-010-agent')
            ->postJson('/api/mcp', [
                'jsonrpc' => '2.0',
                'id' => 10,
                'method' => 'tools/call',
                'params' => [
                    'name' => 'list_queues',
                    'arguments' => [
                        'safe' => 'visible',
                        'token' => 'must-not-be-stored',
                        'password' => 'must-not-be-stored',
                    ],
                ],
            ])
            ->assertOk()
            ->assertJsonPath('result.isError', false);

        $audit = McpToolCall::query()->sole();
        $this->assertSame('SUCCEEDED', $audit->status);
        $this->assertSame('uc-010-agent', $audit->ai_agent_id);
        $this->assertSame(['safe' => 'visible'], $audit->request_payload);
        $this->assertNotNull($audit->finished_at);

        $this->withHeader('Authorization', '')
            ->postJson('/api/mcp', [
                'jsonrpc' => '2.0',
                'id' => 11,
                'method' => 'tools/list',
            ])->assertUnauthorized();
    }

    public function test_uc_011_mutation_replay_is_idempotent_and_conflicting_reuse_is_rejected(): void
    {
        $administrator = $this->administrator();

        $first = $this->asUser($administrator)
            ->withHeader('Idempotency-Key', 'uc011-create')
            ->postJson('/api/queues', ['name' => 'UC 011 Queue'])
            ->assertCreated();

        $replay = $this->asUser($administrator)
            ->withHeader('Idempotency-Key', 'uc011-create')
            ->postJson('/api/queues', ['name' => 'UC 011 Queue'])
            ->assertCreated();

        $this->assertSame($first->json('data'), $replay->json('data'));
        $this->assertDatabaseCount('service_queues', 1);
        $this->assertDatabaseCount('idempotency_records', 1);

        $this->asUser($administrator)
            ->withHeader('Idempotency-Key', 'uc011-create')
            ->postJson('/api/queues', ['name' => 'Different request'])
            ->assertConflict()
            ->assertJsonPath('error.code', 'IDEMPOTENCY_CONFLICT');
    }

    public function test_uc_012_retention_removes_only_expired_terminal_data(): void
    {
        $user = User::factory()->create();
        $queue = $this->queue();
        $expiredEntry = $this->entry($queue, $user, 1, QueueEntryStatus::COMPLETED);
        $expiredEntry->timestamps = false;
        $expiredEntry->updated_at = now()->subDays(91);
        $expiredEntry->save();
        $activeEntry = $this->entry($queue, User::factory()->create(), 2);
        $activeEntry->timestamps = false;
        $activeEntry->updated_at = now()->subDays(91);
        $activeEntry->save();

        $expiredIdempotency = IdempotencyRecord::create([
            'user_id' => $user->id,
            'operation' => 'uc012-expired',
            'idempotency_key' => 'uc012-expired',
            'request_fingerprint' => str_repeat('a', 64),
            'status' => 'SUCCEEDED',
            'response_snapshot' => [],
            'expires_at' => now()->subMinute(),
        ]);
        $inProgressIdempotency = IdempotencyRecord::create([
            'user_id' => $user->id,
            'operation' => 'uc012-in-progress',
            'idempotency_key' => 'uc012-in-progress',
            'request_fingerprint' => str_repeat('b', 64),
            'status' => 'IN_PROGRESS',
            'expires_at' => now()->subMinute(),
        ]);
        $expiredMcp = $this->mcpCall($user, now()->subDays(31));
        $recentMcp = $this->mcpCall($user, now()->subDays(29));

        $this->artisan('queue-assignment:retention')
            ->expectsOutput('Retention complete: entries=1 idempotency=1 mcp=1')
            ->assertSuccessful();

        $this->assertModelMissing($expiredEntry);
        $this->assertModelExists($activeEntry);
        $this->assertModelMissing($expiredIdempotency);
        $this->assertModelExists($inProgressIdempotency);
        $this->assertModelMissing($expiredMcp);
        $this->assertModelExists($recentMcp);
    }

    private function mcpCall(User $user, \DateTimeInterface $startedAt): McpToolCall
    {
        return McpToolCall::create([
            'correlation_id' => fake()->uuid(),
            'tool_name' => 'list_queues',
            'user_id' => $user->id,
            'request_payload' => [],
            'response_payload' => [],
            'status' => 'SUCCEEDED',
            'started_at' => $startedAt,
            'finished_at' => $startedAt,
        ]);
    }
}
