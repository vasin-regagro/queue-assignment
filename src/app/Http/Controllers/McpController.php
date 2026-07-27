<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiException;
use App\Models\McpToolCall;
use App\Models\QueueEntry;
use App\Models\ServiceQueue;
use App\Services\IdempotencyService;
use App\Services\QueueService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Throwable;

class McpController extends Controller
{
    public function __construct(
        private readonly QueueService $queues,
        private readonly IdempotencyService $idempotency,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'jsonrpc' => ['required', 'in:2.0'],
            'id' => ['nullable'],
            'method' => ['required', 'string'],
            'params' => ['nullable', 'array'],
        ]);

        $id = $payload['id'] ?? null;
        $method = $payload['method'];

        if ($method === 'notifications/initialized') {
            return response()->json(null, 202);
        }

        if ($method === 'initialize') {
            return $this->success($id, [
                'protocolVersion' => config('app.mcp_protocol_version', '2025-06-18'),
                'capabilities' => ['tools' => ['listChanged' => false]],
                'serverInfo' => ['name' => 'queue_assignment', 'version' => '0.1.0'],
            ]);
        }

        if ($method === 'tools/list') {
            return $this->success($id, ['tools' => $this->toolDefinitions()]);
        }

        if ($method !== 'tools/call') {
            return $this->error($id, -32601, 'Method not found.');
        }

        $params = $payload['params'] ?? [];
        $tool = is_string($params['name'] ?? null) ? $params['name'] : 'invalid';
        $arguments = is_array($params['arguments'] ?? null) ? $params['arguments'] : [];
        $audit = McpToolCall::create([
            'correlation_id' => $request->attributes->get('correlation_id'),
            'tool_name' => $tool,
            'user_id' => $request->user()->id,
            'ai_agent_id' => $request->header('MCP-Agent-ID'),
            'idempotency_key' => $arguments['idempotencyKey'] ?? null,
            'request_payload' => $this->sanitize($arguments),
            'status' => 'IN_PROGRESS',
            'started_at' => now(),
        ]);

        try {
            $result = $this->callTool($request, $tool, $arguments);
            $audit->update([
                'response_payload' => $this->sanitize($result),
                'status' => 'SUCCEEDED',
                'finished_at' => now(),
            ]);

            return $this->success($id, [
                'content' => [['type' => 'text', 'text' => json_encode($result, JSON_UNESCAPED_UNICODE)]],
                'structuredContent' => $result,
                'isError' => false,
            ]);
        } catch (ApiException $e) {
            $audit->update([
                'status' => 'FAILED',
                'error_code' => $e->errorCode,
                'response_payload' => ['code' => $e->errorCode, 'message' => $e->getMessage()],
                'finished_at' => now(),
            ]);

            return $this->success($id, [
                'content' => [['type' => 'text', 'text' => $e->getMessage()]],
                'structuredContent' => ['code' => $e->errorCode],
                'isError' => true,
            ]);
        } catch (Throwable $e) {
            report($e);
            $audit->update([
                'status' => 'FAILED',
                'error_code' => 'INTERNAL_ERROR',
                'response_payload' => ['code' => 'INTERNAL_ERROR'],
                'finished_at' => now(),
            ]);

            return $this->error($id, -32603, 'Internal error.');
        }
    }

    /** @param array<string, mixed> $arguments
     * @return array<string, mixed>|array<int, array<string, mixed>>
     */
    private function callTool(Request $request, string $tool, array $arguments): array
    {
        $user = $request->user();

        return match ($tool) {
            'list_queues' => $this->queues->listQueues(),
            'join_queue' => $this->idempotency->execute(
                $user,
                'queue.join',
                $arguments['idempotencyKey'] ?? null,
                ['queueId' => $this->integerArgument($arguments, 'queueId')],
                fn (): array => $this->queues->joinQueue(
                    $user,
                    $this->queue($this->integerArgument($arguments, 'queueId')),
                ),
            ),
            'get_current_position' => $this->queues->currentPosition(
                $user,
                $this->queue($this->integerArgument($arguments, 'queueId')),
            ),
            'cancel_queue_entry' => $this->idempotency->execute(
                $user,
                'queue-entry.cancel',
                $arguments['idempotencyKey'] ?? null,
                ['queueEntryId' => $this->integerArgument($arguments, 'queueEntryId')],
                fn (): array => $this->queues->cancelEntry(
                    $user,
                    $this->entry($this->integerArgument($arguments, 'queueEntryId')),
                ),
            ),
            'call_next_user' => $this->idempotency->execute(
                $user,
                'queue.call-next',
                $arguments['idempotencyKey'] ?? null,
                ['queueId' => $this->integerArgument($arguments, 'queueId')],
                fn (): array => $this->queues->callNext(
                    $user,
                    $this->queue($this->integerArgument($arguments, 'queueId')),
                ),
            ),
            'start_service' => $this->idempotency->execute(
                $user,
                'queue-entry.start-service',
                $arguments['idempotencyKey'] ?? null,
                ['queueEntryId' => $this->integerArgument($arguments, 'queueEntryId')],
                fn (): array => $this->queues->startService(
                    $user,
                    $this->entry($this->integerArgument($arguments, 'queueEntryId')),
                ),
            ),
            'complete_service' => $this->idempotency->execute(
                $user,
                'queue-entry.complete-service',
                $arguments['idempotencyKey'] ?? null,
                ['queueEntryId' => $this->integerArgument($arguments, 'queueEntryId')],
                fn (): array => $this->queues->completeService(
                    $user,
                    $this->entry($this->integerArgument($arguments, 'queueEntryId')),
                ),
            ),
            default => throw new ApiException('MCP_TOOL_NOT_FOUND', 'The requested MCP tool does not exist.', 404),
        };
    }

    /** @return array<int, array<string, mixed>> */
    private function toolDefinitions(): array
    {
        $queueId = ['type' => 'object', 'properties' => ['queueId' => ['type' => 'integer']], 'required' => ['queueId']];
        $entryId = ['type' => 'object', 'properties' => ['queueEntryId' => ['type' => 'integer']], 'required' => ['queueEntryId']];
        $mutatingQueue = $this->withIdempotency($queueId);
        $mutatingEntry = $this->withIdempotency($entryId);

        return [
            ['name' => 'list_queues', 'description' => 'List queues and their states.', 'inputSchema' => ['type' => 'object', 'properties' => new \stdClass]],
            ['name' => 'join_queue', 'description' => 'Join an open queue as the authenticated user.', 'inputSchema' => $mutatingQueue],
            ['name' => 'get_current_position', 'description' => 'Get the authenticated user queue position.', 'inputSchema' => $queueId],
            ['name' => 'cancel_queue_entry', 'description' => 'Cancel an owned WAITING or CALLED entry.', 'inputSchema' => $mutatingEntry],
            ['name' => 'call_next_user', 'description' => 'Call the next FIFO entry as an assigned operator.', 'inputSchema' => $mutatingQueue],
            ['name' => 'start_service', 'description' => 'Start service for a CALLED entry.', 'inputSchema' => $mutatingEntry],
            ['name' => 'complete_service', 'description' => 'Complete a SERVING entry.', 'inputSchema' => $mutatingEntry],
        ];
    }

    /** @param array<string, mixed> $schema
     * @return array<string, mixed>
     */
    private function withIdempotency(array $schema): array
    {
        $schema['properties']['idempotencyKey'] = ['type' => 'string', 'minLength' => 1, 'maxLength' => 128];
        $schema['required'][] = 'idempotencyKey';

        return $schema;
    }

    /** @param array<string, mixed> $arguments */
    private function integerArgument(array $arguments, string $name): int
    {
        $value = $arguments[$name] ?? null;

        if (! is_int($value) && ! (is_string($value) && ctype_digit($value))) {
            throw new ApiException('MCP_INVALID_ARGUMENT', "$name must be an integer.", 422);
        }

        return (int) $value;
    }

    private function queue(int $id): ServiceQueue
    {
        return ServiceQueue::find($id) ?? throw ApiException::notFound('The queue was not found.');
    }

    private function entry(int $id): QueueEntry
    {
        return QueueEntry::find($id) ?? throw ApiException::notFound('The queue entry was not found.');
    }

    /** @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function sanitize(array $payload): array
    {
        return Arr::except($payload, ['token', 'accessToken', 'password', 'authorization']);
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function success(mixed $id, array $result): JsonResponse
    {
        return response()->json(['jsonrpc' => '2.0', 'id' => $id, 'result' => $result]);
    }

    private function error(mixed $id, int $code, string $message): JsonResponse
    {
        return response()->json(['jsonrpc' => '2.0', 'id' => $id, 'error' => compact('code', 'message')]);
    }
}
