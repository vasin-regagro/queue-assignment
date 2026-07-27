<?php

namespace App\Http\Controllers;

use App\Models\ServiceQueue;
use App\Models\User;
use App\Services\IdempotencyService;
use App\Services\QueueService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OperatorAssignmentController extends Controller
{
    public function __construct(
        private readonly QueueService $queues,
        private readonly IdempotencyService $idempotency,
    ) {}

    public function store(Request $request, ServiceQueue $queue, User $operator): JsonResponse
    {
        $parameters = ['queueId' => $queue->id, 'operatorUserId' => $operator->id];
        $result = $this->idempotency->execute(
            $request->user(),
            'operator.assign',
            $request->header('Idempotency-Key'),
            $parameters,
            fn (): array => $this->queues->assignOperator($request->user(), $queue, $operator),
        );

        return response()->json(['data' => $result], 201);
    }

    public function destroy(Request $request, ServiceQueue $queue, User $operator): JsonResponse
    {
        $parameters = ['queueId' => $queue->id, 'operatorUserId' => $operator->id];
        $result = $this->idempotency->execute(
            $request->user(),
            'operator.unassign',
            $request->header('Idempotency-Key'),
            $parameters,
            fn (): array => $this->queues->unassignOperator($request->user(), $queue, $operator),
        );

        return response()->json(['data' => $result]);
    }
}
