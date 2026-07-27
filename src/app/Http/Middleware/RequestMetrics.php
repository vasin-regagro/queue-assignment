<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class RequestMetrics
{
    public function handle(Request $request, Closure $next): Response
    {
        $started = hrtime(true);
        $response = $next($request);
        $durationMs = (int) ((hrtime(true) - $started) / 1_000_000);
        $statusClass = intdiv($response->getStatusCode(), 100).'xx';

        try {
            Redis::pipeline(function ($pipe) use ($statusClass, $durationMs): void {
                $pipe->incr('metrics:http_requests_total');
                $pipe->incr("metrics:http_responses_$statusClass");
                $pipe->incrby('metrics:http_duration_ms_sum', $durationMs);
            });
        } catch (Throwable) {
            // Metrics must never make a business request fail.
        }

        return $response;
    }
}
