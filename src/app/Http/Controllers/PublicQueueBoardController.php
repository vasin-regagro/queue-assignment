<?php

namespace App\Http\Controllers;

use App\Services\QueueService;
use Illuminate\Contracts\View\View;

class PublicQueueBoardController extends Controller
{
    public function __construct(private readonly QueueService $queues) {}

    public function __invoke(): View
    {
        return view('queues-board', [
            'queues' => $this->queues->publicBoard(),
            'generatedAt' => now(),
        ]);
    }
}
