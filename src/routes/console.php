<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('queue-assignment:retention', function (): void {
    $now = now();

    $entries = DB::table('queue_entries')
        ->whereIn('status', ['COMPLETED', 'CANCELLED'])
        ->where('updated_at', '<', $now->copy()->subDays(90))
        ->delete();
    $idempotency = DB::table('idempotency_records')
        ->where('status', '!=', 'IN_PROGRESS')
        ->where('expires_at', '<', $now)
        ->delete();
    $mcp = DB::table('mcp_tool_calls')
        ->where('started_at', '<', $now->copy()->subDays(30))
        ->delete();

    $this->info("Retention complete: entries=$entries idempotency=$idempotency mcp=$mcp");
})->purpose('Delete expired queue, idempotency, and MCP audit records');

Schedule::command('queue-assignment:retention')->dailyAt('02:00')->withoutOverlapping();
