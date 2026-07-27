<?php

namespace Tests\Feature;

use App\Http\Controllers\LegacyMcpSseController;
use App\Models\McpAccessToken;
use App\Models\McpToolCall;
use App\Models\ServiceQueue;
use App\Models\User;
use App\Services\JwtService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\TestCase;

class McpTest extends TestCase
{
    use RefreshDatabase;

    public function test_mcp_lists_and_calls_only_supported_tools_with_audit(): void
    {
        $user = User::factory()->create();
        $queue = ServiceQueue::create(['name' => 'MCP queue', 'status' => 'OPEN', 'next_ticket_number' => 1]);

        $tools = $this->withTokenFor($user)->postJson('/api/mcp', [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'tools/list',
        ])->assertOk();

        $names = collect($tools->json('result.tools'))->pluck('name')->all();
        $this->assertSame([
            'list_queues',
            'join_queue',
            'get_current_position',
            'cancel_queue_entry',
            'call_next_user',
            'start_service',
            'complete_service',
        ], $names);

        $this->withTokenFor($user)
            ->withHeader('MCP-Agent-ID', 'test-agent')
            ->postJson('/api/mcp', [
                'jsonrpc' => '2.0',
                'id' => 2,
                'method' => 'tools/call',
                'params' => ['name' => 'list_queues', 'arguments' => []],
            ])
            ->assertOk()
            ->assertJsonPath('result.isError', false);

        $this->assertDatabaseHas('mcp_tool_calls', [
            'tool_name' => 'list_queues',
            'user_id' => $user->id,
            'ai_agent_id' => 'test-agent',
            'status' => 'SUCCEEDED',
        ]);

        $this->withTokenFor($user)
            ->withHeader('MCP-Agent-ID', 'test-agent')
            ->postJson('/api/mcp', [
                'jsonrpc' => '2.0',
                'id' => 3,
                'method' => 'tools/call',
                'params' => [
                    'name' => 'join_queue',
                    'arguments' => [
                        'queueId' => $queue->id,
                        'idempotencyKey' => 'mcp-join-1',
                    ],
                ],
            ])
            ->assertOk()
            ->assertJsonPath('result.isError', false)
            ->assertJsonPath('result.structuredContent.ticketNumber', 1);

        $this->assertSame(2, McpToolCall::count());
        $this->assertDatabaseHas('queue_entries', [
            'queue_id' => $queue->id,
            'user_id' => $user->id,
            'status' => 'WAITING',
        ]);
    }

    public function test_mcp_post_alias_accepts_revocable_query_token(): void
    {
        $user = User::factory()->create();
        $plainTextToken = 'test-query-token';
        $token = McpAccessToken::create([
            'user_id' => $user->id,
            'name' => 'Codex test',
            'token_hash' => hash('sha256', $plainTextToken),
        ]);

        $this->postJson('/api/sse?token='.$plainTextToken, [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'initialize',
            'params' => [
                'protocolVersion' => '2025-06-18',
                'capabilities' => [],
                'clientInfo' => ['name' => 'Codex', 'version' => '1.0'],
            ],
        ])
            ->assertOk()
            ->assertJsonPath('result.serverInfo.name', 'queue_assignment');

        $this->assertNotNull($token->fresh()->last_used_at);

        $token->update(['revoked_at' => now()]);

        $this->postJson('/api/sse?token='.$plainTextToken, [
            'jsonrpc' => '2.0',
            'id' => 2,
            'method' => 'tools/list',
        ])->assertUnauthorized();
    }

    public function test_legacy_sse_get_returns_a_streamed_response(): void
    {
        $user = User::factory()->create();
        $request = Request::create('/api/sse?token=test-query-token', 'GET');
        $request->setUserResolver(fn (): User => $user);

        Redis::shouldReceive('setex')
            ->once()
            ->withArgs(fn (string $key, int $ttl, string $userId): bool => str_starts_with($key, 'mcp:sse:session:')
                && $ttl === 3600
                && $userId === (string) $user->id);

        $response = app(LegacyMcpSseController::class)($request);

        $this->assertInstanceOf(StreamedResponse::class, $response);
        $this->assertSame('text/event-stream', $response->headers->get('Content-Type'));
        $this->assertSame('no', $response->headers->get('X-Accel-Buffering'));
    }

    private function withTokenFor(User $user): static
    {
        return $this->withHeader('Authorization', 'Bearer '.app(JwtService::class)->issue($user));
    }
}
