<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Exceptions\ApiException;
use App\Models\OperatorAssignment;
use App\Models\QueueEntry;
use App\Models\ServiceQueue;
use App\Models\User;

class AuthorizationService
{
    public function requireRole(User $user, UserRole $role): void
    {
        if (! $user->hasRole($role)) {
            throw ApiException::forbidden("The {$role->value} role is required.");
        }
    }

    public function requireOwner(User $user, QueueEntry $entry): void
    {
        if ($entry->user_id !== $user->id) {
            throw ApiException::forbidden('Only the entry owner can perform this action.');
        }
    }

    public function requireOperatorAssignment(User $user, ServiceQueue $queue): void
    {
        $this->requireRole($user, UserRole::OPERATOR);

        $assigned = OperatorAssignment::query()
            ->where('operator_user_id', $user->id)
            ->where('queue_id', $queue->id)
            ->where('active_marker', 'ACTIVE')
            ->exists();

        if (! $assigned) {
            throw ApiException::forbidden('The operator is not assigned to this queue.');
        }
    }
}
