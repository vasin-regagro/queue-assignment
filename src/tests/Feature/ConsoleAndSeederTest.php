<?php

namespace Tests\Feature;

use App\Enums\QueueEntryStatus;
use App\Models\IdempotencyRecord;
use App\Models\McpAccessToken;
use App\Models\McpToolCall;
use App\Models\QueueEntry;
use App\Models\ServiceQueue;
use App\Models\User;
use Database\Seeders\DefaultQueueSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ConsoleAndSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_queue_seeder_is_idempotent_and_preserves_runtime_state(): void
    {
        $this->seed(DefaultQueueSeeder::class);

        $queue = ServiceQueue::query()->where('name', 'Default')->firstOrFail();
        $this->assertSame('OPEN', $queue->status->value);
        $this->assertSame(1, $queue->next_ticket_number);

        $queue->update(['status' => 'CLOSED', 'next_ticket_number' => 42]);
        $this->seed(DefaultQueueSeeder::class);

        $this->assertDatabaseCount('service_queues', 1);
        $this->assertDatabaseHas('service_queues', [
            'name' => 'Default',
            'status' => 'CLOSED',
            'next_ticket_number' => 42,
        ]);
    }

    public function test_mcp_token_create_and_revoke_commands(): void
    {
        $user = User::factory()->create(['email' => 'admin@example.test']);

        $exitCode = Artisan::call('mcp:token:create', [
            'email' => $user->email,
            '--name' => 'Automated test',
            '--days' => '7',
            '--machine' => true,
        ]);
        $payload = json_decode(trim(Artisan::output()), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(0, $exitCode);
        $this->assertSame(64, strlen($payload['token']));
        $this->assertDatabaseHas('mcp_access_tokens', [
            'id' => $payload['id'],
            'user_id' => $user->id,
            'name' => 'Automated test',
            'token_hash' => hash('sha256', $payload['token']),
        ]);
        $this->assertNotNull(McpAccessToken::findOrFail($payload['id'])->expires_at);

        $this->assertSame(0, Artisan::call('mcp:token:revoke', ['id' => $payload['id']]));
        $this->assertNotNull(McpAccessToken::findOrFail($payload['id'])->revoked_at);
    }

    public function test_mcp_token_commands_reject_invalid_input(): void
    {
        $guest = User::factory()->create([
            'email' => null,
            'is_guest' => true,
            'guest_expires_at' => now()->addDay(),
        ]);
        $user = User::factory()->create();

        $this->assertSame(1, Artisan::call('mcp:token:create', ['email' => 'missing@example.test']));
        $this->assertSame(1, Artisan::call('mcp:token:create', [
            'email' => $guest->email ?? 'guest@example.test',
        ]));
        $this->assertSame(1, Artisan::call('mcp:token:create', [
            'email' => $user->email,
            '--days' => '0',
        ]));
        $this->assertSame(1, Artisan::call('mcp:token:revoke', ['id' => 999999]));
        $this->assertDatabaseCount('mcp_access_tokens', 0);
    }

    public function test_retention_command_deletes_only_expired_terminal_records(): void
    {
        $user = User::factory()->create();
        $queue = ServiceQueue::create([
            'name' => 'Retention',
            'status' => 'OPEN',
            'next_ticket_number' => 3,
        ]);

        $expiredEntry = $this->entry($queue, $user, 1, QueueEntryStatus::COMPLETED, now()->subDays(91));
        $activeEntry = $this->entry($queue, $user, 2, QueueEntryStatus::WAITING, now()->subDays(91));

        $expiredIdempotency = IdempotencyRecord::create([
            'user_id' => $user->id,
            'operation' => 'expired',
            'idempotency_key' => 'expired',
            'request_fingerprint' => str_repeat('a', 64),
            'status' => 'SUCCEEDED',
            'response_snapshot' => [],
            'expires_at' => now()->subMinute(),
        ]);
        $inProgressIdempotency = IdempotencyRecord::create([
            'user_id' => $user->id,
            'operation' => 'in-progress',
            'idempotency_key' => 'in-progress',
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

    private function entry(
        ServiceQueue $queue,
        User $user,
        int $ticket,
        QueueEntryStatus $status,
        \DateTimeInterface $updatedAt,
    ): QueueEntry {
        $entry = QueueEntry::create([
            'queue_id' => $queue->id,
            'user_id' => $user->id,
            'ticket_number' => $ticket,
            'status' => $status,
            'active_marker' => $status === QueueEntryStatus::WAITING ? 'ACTIVE' : null,
            'joined_at' => $updatedAt,
            'completed_at' => $status === QueueEntryStatus::COMPLETED ? $updatedAt : null,
        ]);
        $entry->timestamps = false;
        $entry->updated_at = $updatedAt;
        $entry->save();

        return $entry;
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
