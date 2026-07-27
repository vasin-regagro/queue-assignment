<?php

use App\Http\Controllers\PublicQueueBoardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'service' => 'queue_assignment',
        'status' => 'ok',
        'documentation' => '/api/health',
    ]);
});

Route::get('/queues-board', PublicQueueBoardController::class)->name('queues.board');
