<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\IdempotencyRecord;
use App\Models\McpToolCall;
use App\Models\QueueEntry;
use App\Models\ServiceQueue;
use App\Services\AuthorizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Throwable;

class MetricsController extends Controller
{
    public function __construct(private readonly AuthorizationService $authorization) {}

    public function __invoke(Request $request): JsonResponse
    {
        $this->authorization->requireRole($request->user(), UserRole::ADMINISTRATOR);

        $transport = [];

        try {
            foreach ([
                'http_requests_total',
                'http_responses_2xx',
                'http_responses_4xx',
                'http_responses_5xx',
                'http_duration_ms_sum',
            ] as $name) {
                $transport[$name] = (int) (Redis::get("metrics:$name") ?? 0);
            }
        } catch (Throwable) {
            $transport['available'] = false;
        }

        return response()->json([
            'data' => [
                'transport' => $transport,
                'domain' => [
                    'queues' => ServiceQueue::count(),
                    'activeEntries' => QueueEntry::query()->where('active_marker', 'ACTIVE')->count(),
                    'waitingEntries' => QueueEntry::query()->where('status', 'WAITING')->count(),
                    'mcpCalls' => McpToolCall::count(),
                    'mcpFailures' => McpToolCall::query()->where('status', 'FAILED')->count(),
                    'idempotencyConflictsInProgress' => IdempotencyRecord::query()->where('status', 'IN_PROGRESS')->count(),
                ],
                'timestamp' => now()->toISOString(),
            ],
        ]);
    }
}
