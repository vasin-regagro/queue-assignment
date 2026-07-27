<?php

namespace App\Http\Controllers;

use App\Enums\QueueStatus;
use App\Models\ServiceQueue;
use App\Services\IdempotencyService;
use App\Services\QueueService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QueueController extends Controller
{
    public function __construct(
        private readonly QueueService $queues,
        private readonly IdempotencyService $idempotency,
    ) {}

    public function index(): JsonResponse
    {
        return response()->json(['data' => $this->queues->listQueues()]);
    }

    public function show(ServiceQueue $queue): JsonResponse
    {
        return response()->json(['data' => $this->queues->queueData($queue)]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:255']]);
        $result = $this->idempotency->execute(
            $request->user(),
            'queue.create',
            $request->header('Idempotency-Key'),
            $data,
            fn (): array => $this->queues->createQueue($request->user(), $data['name']),
        );

        return response()->json(['data' => $result], 201);
    }

    public function open(Request $request, ServiceQueue $queue): JsonResponse
    {
        return $this->changeStatus($request, $queue, QueueStatus::OPEN);
    }

    public function close(Request $request, ServiceQueue $queue): JsonResponse
    {
        return $this->changeStatus($request, $queue, QueueStatus::CLOSED);
    }

    private function changeStatus(Request $request, ServiceQueue $queue, QueueStatus $status): JsonResponse
    {
        $result = $this->idempotency->execute(
            $request->user(),
            'queue.status.'.$status->value,
            $request->header('Idempotency-Key'),
            ['queueId' => $queue->id, 'status' => $status->value],
            fn (): array => $this->queues->changeQueueStatus($request->user(), $queue, $status),
        );

        return response()->json(['data' => $result]);
    }
}
