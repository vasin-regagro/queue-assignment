<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\IdempotencyRecord;
use App\Models\User;
use Closure;
use Illuminate\Support\Facades\DB;

class IdempotencyService
{
    /**
     * @param  array<string, mixed>  $parameters
     * @param  Closure(): array<string, mixed>  $callback
     * @return array<string, mixed>
     */
    public function execute(
        User $user,
        string $operation,
        ?string $key,
        array $parameters,
        Closure $callback,
    ): array {
        if (! is_string($key) || $key === '' || strlen($key) > 128) {
            throw new ApiException('IDEMPOTENCY_KEY_REQUIRED', 'A valid idempotency key is required.', 422);
        }

        $fingerprint = hash('sha256', json_encode($this->canonicalize($parameters), JSON_THROW_ON_ERROR));

        return DB::transaction(function () use ($user, $operation, $key, $fingerprint, $callback): array {
            $now = now();

            $inserted = DB::table('idempotency_records')->insertOrIgnore([
                'user_id' => $user->id,
                'operation' => $operation,
                'idempotency_key' => $key,
                'request_fingerprint' => $fingerprint,
                'status' => 'IN_PROGRESS',
                'expires_at' => $now->copy()->addHours(24),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            /** @var IdempotencyRecord $record */
            $record = IdempotencyRecord::query()
                ->where('user_id', $user->id)
                ->where('operation', $operation)
                ->where('idempotency_key', $key)
                ->lockForUpdate()
                ->firstOrFail();

            if (! hash_equals($record->request_fingerprint, $fingerprint)) {
                throw ApiException::conflict(
                    'IDEMPOTENCY_CONFLICT',
                    'The idempotency key was already used with different parameters.',
                );
            }

            if ($record->status === 'SUCCEEDED') {
                return $record->response_snapshot ?? [];
            }

            if ($inserted === 0 && $record->created_at && $record->status === 'IN_PROGRESS') {
                $age = $record->created_at->diffInSeconds(now());
                if ($age > 30) {
                    $record->delete();
                    throw ApiException::conflict('IDEMPOTENCY_STALE', 'A stale operation reservation was cleared; retry.');
                }

                throw ApiException::conflict('IDEMPOTENCY_IN_PROGRESS', 'The original operation is still in progress.');
            }

            $result = $callback();
            $record->forceFill([
                'status' => 'SUCCEEDED',
                'response_snapshot' => $result,
                'expires_at' => now()->addHours(24),
            ])->save();

            return $result;
        }, 3);
    }

    private function canonicalize(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        if (array_is_list($value)) {
            return array_map(fn (mixed $item): mixed => $this->canonicalize($item), $value);
        }

        ksort($value);

        foreach ($value as $key => $item) {
            $value[$key] = $this->canonicalize($item);
        }

        return $value;
    }
}
