<?php

namespace App\Console\Commands;

use App\Models\McpAccessToken;
use App\Models\User;
use Illuminate\Console\Command;

class CreateMcpAccessToken extends Command
{
    protected $signature = 'mcp:token:create
        {email : Email of the user represented by the MCP client}
        {--name=Codex : Human-readable token name}
        {--days= : Optional positive lifetime in days}
        {--machine : Print a single JSON object for automation}';

    protected $description = 'Create a revocable MCP access token and print its secret once';

    public function handle(): int
    {
        $user = User::query()
            ->where('email', $this->argument('email'))
            ->where('is_guest', false)
            ->first();

        if (! $user) {
            $this->error('A permanent user with this email was not found.');

            return self::FAILURE;
        }

        $days = $this->option('days');

        if ($days !== null && (! ctype_digit((string) $days) || (int) $days < 1)) {
            $this->error('--days must be a positive integer.');

            return self::FAILURE;
        }

        $plainTextToken = bin2hex(random_bytes(32));
        $record = McpAccessToken::create([
            'user_id' => $user->id,
            'name' => (string) $this->option('name'),
            'token_hash' => hash('sha256', $plainTextToken),
            'expires_at' => $days === null ? null : now()->addDays((int) $days),
        ]);

        if ($this->option('machine')) {
            $this->line(json_encode([
                'id' => $record->id,
                'token' => $plainTextToken,
                'expiresAt' => $record->expires_at?->toIso8601String(),
            ], JSON_THROW_ON_ERROR));
        } else {
            $this->warn('Store this token now. It will not be shown again.');
            $this->line($plainTextToken);
            $this->line("Token ID: {$record->id}");
        }

        return self::SUCCESS;
    }
}
