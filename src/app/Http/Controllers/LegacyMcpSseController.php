<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LegacyMcpSseController extends Controller
{
    public function __invoke(Request $request): StreamedResponse
    {
        $sessionId = (string) Str::uuid();
        $sessionKey = $this->sessionKey($sessionId);
        $queueKey = $this->queueKey($sessionId);
        $token = (string) $request->query('token');
        $endpoint = $request->getSchemeAndHttpHost().'/api/sse/messages?'.http_build_query([
            'sessionId' => $sessionId,
            'token' => $token,
        ], '', '&', PHP_QUERY_RFC3986);

        Redis::setex($sessionKey, 3600, (string) $request->user()->id);

        return response()->stream(function () use ($endpoint, $queueKey, $sessionKey): void {
            ignore_user_abort(true);
            $startedAt = time();

            echo "event: endpoint\n";
            echo 'data: '.$endpoint."\n\n";
            $this->flush();

            try {
                while (! connection_aborted() && time() - $startedAt < 3600) {
                    $message = Redis::connection()->blpop([$queueKey], 15);

                    if (is_array($message) && isset($message[1])) {
                        echo "event: message\n";
                        echo 'data: '.$message[1]."\n\n";
                    } else {
                        echo ": keepalive\n\n";
                    }

                    $this->flush();
                    Redis::expire($sessionKey, 3600);
                }
            } finally {
                Redis::del($sessionKey, $queueKey);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache, no-transform',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    private function flush(): void
    {
        if (ob_get_level() > 0) {
            ob_flush();
        }

        flush();
    }

    private function sessionKey(string $sessionId): string
    {
        return "mcp:sse:session:$sessionId";
    }

    private function queueKey(string $sessionId): string
    {
        return "mcp:sse:queue:$sessionId";
    }
}
