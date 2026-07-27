<?php

namespace App\Services;

use App\Enums\QueueEntryStatus;
use App\Enums\QueueStatus;
use App\Enums\UserRole;
use App\Exceptions\ApiException;
use App\Models\OperatorAssignment;
use App\Models\QueueEntry;
use App\Models\ServiceQueue;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QueueService
{
    public function __construct(private readonly AuthorizationService $authorization) {}

    /** @return array<int, array<string, mixed>> */
    public function listQueues(): array
    {
        return ServiceQueue::query()
            ->orderBy('id')
            ->get()
            ->map(fn (ServiceQueue $queue): array => $this->queueData($queue))
            ->all();
    }

    /** @return array<int, array<string, mixed>> */
    public function publicBoard(): array
    {
        $activeStatuses = [
            QueueEntryStatus::WAITING->value,
            QueueEntryStatus::CALLED->value,
            QueueEntryStatus::SERVING->value,
        ];

        return ServiceQueue::query()
            ->with(['entries' => function ($query) use ($activeStatuses): void {
                $query
                    ->whereIn('status', $activeStatuses)
                    ->with('user:id,name')
                    ->orderBy('joined_at')
                    ->orderBy('id');
            }])
            ->orderBy('id')
            ->get()
            ->map(fn (ServiceQueue $queue): array => [
                'name' => $queue->name,
                'status' => $queue->status->value,
                'activeCount' => $queue->entries->count(),
                'entries' => $queue->entries
                    ->map(fn (QueueEntry $entry): array => [
                        'ticketNumber' => $entry->ticket_number,
                        'displayName' => $entry->user?->name ?: 'Без имени',
                        'status' => $entry->status->value,
                        'joinedAt' => $entry->joined_at,
                        'calledAt' => $entry->called_at,
                        'serviceStartedAt' => $entry->service_started_at,
                    ])
                    ->all(),
            ])
            ->all();
    }

    /** @return array<string, mixed> */
    public function createQueue(User $administrator, string $name): array
    {
        $this->authorization->requireRole($administrator, UserRole::ADMINISTRATOR);

        $queue = ServiceQueue::create([
            'name' => $name,
            'status' => QueueStatus::CLOSED,
            'next_ticket_number' => 1,
        ]);

        $this->event('QUEUE_CREATED', ['queue_id' => $queue->id, 'administrator_id' => $administrator->id]);

        return $this->queueData($queue);
    }

    /** @return array<string, mixed> */
    public function changeQueueStatus(User $administrator, ServiceQueue $queue, QueueStatus $status): array
    {
        $this->authorization->requireRole($administrator, UserRole::ADMINISTRATOR);

        return DB::transaction(function () use ($administrator, $queue, $status): array {
            /** @var ServiceQueue $locked */
            $locked = ServiceQueue::query()->lockForUpdate()->findOrFail($queue->id);
            $previous = $locked->status;

            if ($previous !== $status) {
                $locked->status = $status;
                $locked->save();
                $this->event($status === QueueStatus::OPEN ? 'QUEUE_OPENED' : 'QUEUE_CLOSED', [
                    'queue_id' => $locked->id,
                    'administrator_id' => $administrator->id,
                    'previous_status' => $previous->value,
                ]);
            }

            return $this->queueData($locked);
        });
    }

    /** @return array<string, mixed> */
    public function assignOperator(User $administrator, ServiceQueue $queue, User $operator): array
    {
        $this->authorization->requireRole($administrator, UserRole::ADMINISTRATOR);
        $this->authorization->requireRole($operator, UserRole::OPERATOR);

        $assignment = OperatorAssignment::query()->firstOrCreate([
            'operator_user_id' => $operator->id,
            'queue_id' => $queue->id,
            'active_marker' => 'ACTIVE',
        ], [
            'assigned_by' => $administrator->id,
            'assigned_at' => now(),
        ]);

        if ($assignment->wasRecentlyCreated) {
            $this->event('OPERATOR_ASSIGNED', [
                'queue_id' => $queue->id,
                'operator_user_id' => $operator->id,
                'administrator_id' => $administrator->id,
            ]);
        }

        return $this->assignmentData($assignment);
    }

    /** @return array<string, mixed> */
    public function unassignOperator(User $administrator, ServiceQueue $queue, User $operator): array
    {
        $this->authorization->requireRole($administrator, UserRole::ADMINISTRATOR);

        /** @var OperatorAssignment|null $assignment */
        $assignment = OperatorAssignment::query()
            ->where('queue_id', $queue->id)
            ->where('operator_user_id', $operator->id)
            ->where('active_marker', 'ACTIVE')
            ->first();

        if (! $assignment) {
            throw ApiException::notFound('The active operator assignment was not found.');
        }

        $assignment->update(['active_marker' => null, 'revoked_at' => now()]);
        $this->event('OPERATOR_UNASSIGNED', [
            'queue_id' => $queue->id,
            'operator_user_id' => $operator->id,
            'administrator_id' => $administrator->id,
        ]);

        return $this->assignmentData($assignment->fresh());
    }

    /** @return array<string, mixed> */
    public function joinQueue(User $user, ServiceQueue $queue): array
    {
        return DB::transaction(function () use ($user, $queue): array {
            /** @var ServiceQueue $lockedQueue */
            $lockedQueue = ServiceQueue::query()->lockForUpdate()->findOrFail($queue->id);

            if ($lockedQueue->status !== QueueStatus::OPEN) {
                throw ApiException::conflict('QUEUE_CLOSED', 'New entries are not accepted in a closed queue.');
            }

            $activeExists = QueueEntry::query()
                ->where('queue_id', $lockedQueue->id)
                ->where('user_id', $user->id)
                ->where('active_marker', 'ACTIVE')
                ->lockForUpdate()
                ->exists();

            if ($activeExists) {
                throw ApiException::conflict('ACTIVE_ENTRY_EXISTS', 'The user already has an active entry in this queue.');
            }

            $ticket = $lockedQueue->next_ticket_number;
            $lockedQueue->increment('next_ticket_number');

            $entry = QueueEntry::create([
                'queue_id' => $lockedQueue->id,
                'user_id' => $user->id,
                'ticket_number' => $ticket,
                'status' => QueueEntryStatus::WAITING,
                'active_marker' => 'ACTIVE',
                'joined_at' => now(),
            ]);

            $data = $this->entryData($entry);
            $data['position'] = $this->positionValue($entry);
            $this->event('QUEUE_ENTRY_JOINED', ['queue_entry_id' => $entry->id, 'user_id' => $user->id]);

            return $data;
        });
    }

    /** @return array<string, mixed> */
    public function currentPosition(User $user, ServiceQueue $queue): array
    {
        /** @var QueueEntry|null $entry */
        $entry = QueueEntry::query()
            ->where('queue_id', $queue->id)
            ->where('user_id', $user->id)
            ->latest('id')
            ->first();

        if (! $entry) {
            throw ApiException::notFound('No queue entry was found for this user.');
        }

        $data = $this->entryData($entry);
        $data['position'] = $entry->status === QueueEntryStatus::WAITING
            ? $this->positionValue($entry)
            : null;

        return $data;
    }

    /** @return array<string, mixed> */
    public function cancelEntry(User $user, QueueEntry $entry): array
    {
        $this->authorization->requireOwner($user, $entry);

        return DB::transaction(function () use ($entry): array {
            /** @var QueueEntry $locked */
            $locked = QueueEntry::query()->lockForUpdate()->findOrFail($entry->id);

            if (! in_array($locked->status, [QueueEntryStatus::WAITING, QueueEntryStatus::CALLED], true)) {
                throw ApiException::conflict('INVALID_STATE_TRANSITION', 'Only WAITING or CALLED entries can be cancelled.');
            }

            $previous = $locked->status;
            $locked->update([
                'status' => QueueEntryStatus::CANCELLED,
                'active_marker' => null,
                'serving_marker' => null,
                'cancelled_at' => now(),
            ]);
            $this->event('QUEUE_ENTRY_CANCELLED', [
                'queue_entry_id' => $locked->id,
                'previous_status' => $previous->value,
            ]);

            return $this->entryData($locked->fresh());
        });
    }

    /** @return array<string, mixed> */
    public function callNext(User $operator, ServiceQueue $queue): array
    {
        $this->authorization->requireOperatorAssignment($operator, $queue);

        return DB::transaction(function () use ($queue): array {
            /** @var QueueEntry|null $entry */
            $entry = QueueEntry::query()
                ->where('queue_id', $queue->id)
                ->where('status', QueueEntryStatus::WAITING)
                ->orderBy('joined_at')
                ->orderBy('id')
                ->lockForUpdate()
                ->first();

            if (! $entry) {
                throw new ApiException('NO_WAITING_ENTRIES', 'The queue has no waiting entries.', 409);
            }

            $entry->update(['status' => QueueEntryStatus::CALLED, 'called_at' => now()]);
            $this->event('NEXT_USER_CALLED', ['queue_entry_id' => $entry->id]);

            return $this->operatorEntryData($entry->fresh(['user']));
        });
    }

    /** @return array<string, mixed> */
    public function startService(User $operator, QueueEntry $entry): array
    {
        $this->authorization->requireOperatorAssignment($operator, $entry->queue);

        return DB::transaction(function () use ($operator, $entry): array {
            /** @var QueueEntry $locked */
            $locked = QueueEntry::query()->lockForUpdate()->findOrFail($entry->id);

            if ($locked->status !== QueueEntryStatus::CALLED) {
                throw ApiException::conflict('INVALID_STATE_TRANSITION', 'Only a CALLED entry can start service.');
            }

            $operatorBusy = QueueEntry::query()
                ->where('operator_user_id', $operator->id)
                ->where('serving_marker', 'SERVING')
                ->lockForUpdate()
                ->exists();

            if ($operatorBusy) {
                throw ApiException::conflict('OPERATOR_BUSY', 'The operator is already serving another entry.');
            }

            $locked->update([
                'status' => QueueEntryStatus::SERVING,
                'operator_user_id' => $operator->id,
                'serving_marker' => 'SERVING',
                'service_started_at' => now(),
            ]);
            $this->event('SERVICE_STARTED', ['queue_entry_id' => $locked->id, 'operator_user_id' => $operator->id]);

            return $this->operatorEntryData($locked->fresh(['user']));
        });
    }

    /** @return array<string, mixed> */
    public function completeService(User $operator, QueueEntry $entry): array
    {
        $this->authorization->requireOperatorAssignment($operator, $entry->queue);

        return DB::transaction(function () use ($operator, $entry): array {
            /** @var QueueEntry $locked */
            $locked = QueueEntry::query()->lockForUpdate()->findOrFail($entry->id);

            if ($locked->status !== QueueEntryStatus::SERVING || $locked->operator_user_id !== $operator->id) {
                throw ApiException::conflict(
                    'INVALID_STATE_TRANSITION',
                    'Only the serving operator can complete a SERVING entry.',
                );
            }

            $locked->update([
                'status' => QueueEntryStatus::COMPLETED,
                'active_marker' => null,
                'serving_marker' => null,
                'completed_at' => now(),
            ]);
            $this->event('SERVICE_COMPLETED', ['queue_entry_id' => $locked->id, 'operator_user_id' => $operator->id]);

            return $this->operatorEntryData($locked->fresh(['user']));
        });
    }

    /** @return array<string, mixed> */
    public function queueData(ServiceQueue $queue): array
    {
        return [
            'id' => $queue->id,
            'name' => $queue->name,
            'status' => $queue->status->value,
            'createdAt' => $queue->created_at?->toISOString(),
            'updatedAt' => $queue->updated_at?->toISOString(),
        ];
    }

    /** @return array<string, mixed> */
    public function entryData(QueueEntry $entry): array
    {
        return [
            'id' => $entry->id,
            'queueId' => $entry->queue_id,
            'ticketNumber' => $entry->ticket_number,
            'status' => $entry->status->value,
            'joinedAt' => $entry->joined_at?->toISOString(),
            'calledAt' => $entry->called_at?->toISOString(),
            'serviceStartedAt' => $entry->service_started_at?->toISOString(),
            'completedAt' => $entry->completed_at?->toISOString(),
            'cancelledAt' => $entry->cancelled_at?->toISOString(),
        ];
    }

    private function positionValue(QueueEntry $entry): int
    {
        return QueueEntry::query()
            ->where('queue_id', $entry->queue_id)
            ->where('status', QueueEntryStatus::WAITING)
            ->where(function ($query) use ($entry): void {
                $query->where('joined_at', '<', $entry->joined_at)
                    ->orWhere(function ($tie) use ($entry): void {
                        $tie->where('joined_at', $entry->joined_at)->where('id', '<', $entry->id);
                    });
            })
            ->count() + 1;
    }

    /** @return array<string, mixed> */
    private function operatorEntryData(QueueEntry $entry): array
    {
        return [
            'id' => $entry->id,
            'queueId' => $entry->queue_id,
            'ticketNumber' => $entry->ticket_number,
            'status' => $entry->status->value,
            'joinedAt' => $entry->joined_at?->toISOString(),
            'displayName' => $entry->user?->name,
            'calledAt' => $entry->called_at?->toISOString(),
            'serviceStartedAt' => $entry->service_started_at?->toISOString(),
            'completedAt' => $entry->completed_at?->toISOString(),
        ];
    }

    /** @return array<string, mixed> */
    private function assignmentData(OperatorAssignment $assignment): array
    {
        return [
            'id' => $assignment->id,
            'queueId' => $assignment->queue_id,
            'operatorUserId' => $assignment->operator_user_id,
            'active' => $assignment->active_marker === 'ACTIVE',
            'assignedAt' => $assignment->assigned_at?->toISOString(),
            'revokedAt' => $assignment->revoked_at?->toISOString(),
        ];
    }

    /** @param array<string, mixed> $context */
    private function event(string $name, array $context): void
    {
        Log::info('domain_event', ['event' => $name, ...$context]);
    }
}
