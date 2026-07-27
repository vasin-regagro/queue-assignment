<?php

namespace App\Console\Commands;

use App\Models\McpAccessToken;
use Illuminate\Console\Command;

class RevokeMcpAccessToken extends Command
{
    protected $signature = 'mcp:token:revoke {id : MCP access token ID}';

    protected $description = 'Revoke an MCP access token';

    public function handle(): int
    {
        $token = McpAccessToken::find($this->argument('id'));

        if (! $token) {
            $this->error('The MCP access token was not found.');

            return self::FAILURE;
        }

        $token->forceFill(['revoked_at' => now()])->save();
        $this->info("MCP access token {$token->id} was revoked.");

        return self::SUCCESS;
    }
}
