<?php

namespace App\Http\Controllers;

use App\Models\QueueEntry;
use App\Models\ServiceQueue;
use App\Services\IdempotencyService;
use App\Services\QueueService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QueueEntryController extends Controller
{
    public function __construct(
        private readonly QueueService $queues,
        private readonly IdempotencyService $idempotency,
    ) {}

    public function join(Request $request, ServiceQueue $queue): JsonResponse
    {
        $result = $this->mutate(
            $request,
            'queue.join',
            ['queueId' => $queue->id],
            fn (): array => $this->queues->joinQueue($request->user(), $queue),
        );

        return response()->json(['data' => $result], 201);
    }

    public function position(Request $request, ServiceQueue $queue): JsonResponse
    {
        return response()->json(['data' => $this->queues->currentPosition($request->user(), $queue)]);
    }

    public function cancel(Request $request, QueueEntry $entry): JsonResponse
    {
        $result = $this->mutate(
            $request,
            'queue-entry.cancel',
            ['queueEntryId' => $entry->id],
            fn (): array => $this->queues->cancelEntry($request->user(), $entry),
        );

        return response()->json(['data' => $result]);
    }

    public function callNext(Request $request, ServiceQueue $queue): JsonResponse
    {
        $result = $this->mutate(
            $request,
            'queue.call-next',
            ['queueId' => $queue->id],
            fn (): array => $this->queues->callNext($request->user(), $queue),
        );

        return response()->json(['data' => $result]);
    }

    public function startService(Request $request, QueueEntry $entry): JsonResponse
    {
        $result = $this->mutate(
            $request,
            'queue-entry.start-service',
            ['queueEntryId' => $entry->id],
            fn (): array => $this->queues->startService($request->user(), $entry),
        );

        return response()->json(['data' => $result]);
    }

    public function completeService(Request $request, QueueEntry $entry): JsonResponse
    {
        $result = $this->mutate(
            $request,
            'queue-entry.complete-service',
            ['queueEntryId' => $entry->id],
            fn (): array => $this->queues->completeService($request->user(), $entry),
        );

        return response()->json(['data' => $result]);
    }

    /**
     * @param  array<string, mixed>  $parameters
     * @param  \Closure(): array<string, mixed>  $callback
     * @return array<string, mixed>
     */
    private function mutate(Request $request, string $operation, array $parameters, \Closure $callback): array
    {
        return $this->idempotency->execute(
            $request->user(),
            $operation,
            $request->header('Idempotency-Key'),
            $parameters,
            $callback,
        );
    }
}
