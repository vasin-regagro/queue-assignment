<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\LegacyMcpMessageController;
use App\Http\Controllers\LegacyMcpSseController;
use App\Http\Controllers\McpController;
use App\Http\Controllers\MetricsController;
use App\Http\Controllers\OperatorAssignmentController;
use App\Http\Controllers\QueueController;
use App\Http\Controllers\QueueEntryController;
use Illuminate\Support\Facades\Route;

Route::get('/health', HealthController::class);

Route::prefix('auth')->group(function (): void {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/guest', [AuthController::class, 'guest']);
});

Route::middleware('jwt')->group(function (): void {
    Route::get('/metrics', MetricsController::class);
    Route::get('/queues', [QueueController::class, 'index']);
    Route::get('/queues/{queue}', [QueueController::class, 'show']);
    Route::post('/queues', [QueueController::class, 'store']);
    Route::post('/queues/{queue}/open', [QueueController::class, 'open']);
    Route::post('/queues/{queue}/close', [QueueController::class, 'close']);

    Route::post('/queues/{queue}/operators/{operator}', [OperatorAssignmentController::class, 'store']);
    Route::delete('/queues/{queue}/operators/{operator}', [OperatorAssignmentController::class, 'destroy']);

    Route::post('/queues/{queue}/entries', [QueueEntryController::class, 'join']);
    Route::get('/queues/{queue}/position', [QueueEntryController::class, 'position']);
    Route::post('/entries/{entry}/cancel', [QueueEntryController::class, 'cancel']);
    Route::post('/queues/{queue}/call-next', [QueueEntryController::class, 'callNext']);
    Route::post('/entries/{entry}/start-service', [QueueEntryController::class, 'startService']);
    Route::post('/entries/{entry}/complete-service', [QueueEntryController::class, 'completeService']);
});

Route::post('/mcp', McpController::class)->middleware('mcp.auth');
Route::post('/sse', McpController::class)->middleware('mcp.auth');
Route::get('/sse', LegacyMcpSseController::class)->middleware('mcp.auth');
Route::post('/sse/messages', LegacyMcpMessageController::class)->middleware('mcp.auth');
