<?php

namespace Tests\Feature;

use App\Exceptions\ApiException;
use App\Http\Controllers\LegacyMcpMessageController;
use App\Models\McpAccessToken;
use App\Models\McpToolCall;
use App\Models\User;
use App\Services\JwtService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use Tests\TestCase;

class McpProtocolTest extends TestCase
{
    use RefreshDatabase;

    public function test_initialize_notification_and_unknown_method_follow_json_rpc_contract(): void
    {
        $user = User::factory()->create();

        $this->actingAsMcp($user)->postJson('/api/mcp', [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'initialize',
            'params' => [],
        ])
            ->assertOk()
            ->assertJsonPath('result.serverInfo.name', 'queue_assignment')
            ->assertJsonPath('result.capabilities.tools.listChanged', false);

        $this->actingAsMcp($user)->postJson('/api/mcp', [
            'jsonrpc' => '2.0',
            'method' => 'notifications/initialized',
        ])->assertStatus(202);

        $this->actingAsMcp($user)->postJson('/api/mcp', [
            'jsonrpc' => '2.0',
            'id' => 2,
            'method' => 'unknown/method',
        ])
            ->assertOk()
            ->assertJsonPath('error.code', -32601)
            ->assertJsonPath('error.message', 'Method not found.');
    }

    public function test_unknown_tool_and_invalid_arguments_are_audited_as_failures(): void
    {
        $user = User::factory()->create();

        $this->actingAsMcp($user)->postJson('/api/mcp', [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'tools/call',
            'params' => [
                'name' => 'does_not_exist',
                'arguments' => ['token' => 'must-not-be-audited'],
            ],
        ])
            ->assertOk()
            ->assertJsonPath('result.isError', true)
            ->assertJsonPath('result.structuredContent.code', 'MCP_TOOL_NOT_FOUND');

        $this->actingAsMcp($user)->postJson('/api/mcp', [
            'jsonrpc' => '2.0',
            'id' => 2,
            'method' => 'tools/call',
            'params' => [
                'name' => 'get_current_position',
                'arguments' => ['queueId' => 'not-an-integer'],
            ],
        ])
            ->assertOk()
            ->assertJsonPath('result.isError', true)
            ->assertJsonPath('result.structuredContent.code', 'MCP_INVALID_ARGUMENT');

        $this->assertDatabaseCount('mcp_tool_calls', 2);
        $this->assertSame(2, McpToolCall::query()->where('status', 'FAILED')->count());
        $this->assertSame([], McpToolCall::query()->firstOrFail()->request_payload);
    }

    public function test_expired_access_token_and_expired_guest_subject_are_rejected(): void
    {
        $user = User::factory()->create();
        McpAccessToken::create([
            'user_id' => $user->id,
            'name' => 'Expired',
            'token_hash' => hash('sha256', 'expired-token'),
            'expires_at' => now()->subSecond(),
        ]);

        $this->postJson('/api/mcp?token=expired-token', [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'tools/list',
        ])
            ->assertUnauthorized()
            ->assertJsonPath('error.message', 'The MCP access token is invalid or expired.');

        $guest = User::factory()->create([
            'email' => null,
            'is_guest' => true,
            'guest_expires_at' => now()->subSecond(),
        ]);
        McpAccessToken::create([
            'user_id' => $guest->id,
            'name' => 'Expired guest',
            'token_hash' => hash('sha256', 'guest-token'),
        ]);

        $this->postJson('/api/mcp?token=guest-token', [
            'jsonrpc' => '2.0',
            'id' => 2,
            'method' => 'tools/list',
        ])
            ->assertUnauthorized()
            ->assertJsonPath('error.message', 'The MCP token subject is unavailable.');
    }

    public function test_legacy_message_is_queued_for_the_authenticated_session(): void
    {
        $user = User::factory()->create();
        $sessionId = (string) Str::uuid();
        $request = Request::create("/api/sse/messages?sessionId=$sessionId", 'POST', [
            'jsonrpc' => '2.0',
            'id' => 7,
            'method' => 'tools/list',
        ]);
        $request->setUserResolver(fn (): User => $user);
        $request->attributes->set('correlation_id', (string) Str::uuid());

        Redis::shouldReceive('get')
            ->once()
            ->with("mcp:sse:session:$sessionId")
            ->andReturn((string) $user->id);
        Redis::shouldReceive('rpush')
            ->once()
            ->withArgs(fn (string $key, string $payload): bool => $key === "mcp:sse:queue:$sessionId"
                && str_contains($payload, '"tools"'));
        Redis::shouldReceive('expire')
            ->once()
            ->with("mcp:sse:session:$sessionId", 3600);

        $response = app(LegacyMcpMessageController::class)($request);

        $this->assertSame(202, $response->getStatusCode());
    }

    public function test_legacy_message_rejects_invalid_or_foreign_session(): void
    {
        $user = User::factory()->create();
        $request = Request::create('/api/sse/messages?sessionId=invalid', 'POST');
        $request->setUserResolver(fn (): User => $user);

        try {
            app(LegacyMcpMessageController::class)($request);
            $this->fail('Invalid legacy SSE session was accepted.');
        } catch (ApiException $exception) {
            $this->assertSame('NOT_FOUND', $exception->errorCode);
        }

        $sessionId = (string) Str::uuid();
        $request = Request::create("/api/sse/messages?sessionId=$sessionId", 'POST');
        $request->setUserResolver(fn (): User => $user);
        Redis::shouldReceive('get')->once()->andReturn('different-user');

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('The MCP SSE session was not found.');
        app(LegacyMcpMessageController::class)($request);
    }

    private function actingAsMcp(User $user): static
    {
        return $this->withHeader('Authorization', 'Bearer '.app(JwtService::class)->issue($user));
    }
}
