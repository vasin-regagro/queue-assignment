<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class LegacyMcpMessageController extends Controller
{
    public function __construct(private readonly McpController $mcp) {}

    public function __invoke(Request $request): Response
    {
        $sessionId = (string) $request->query('sessionId');

        if (! Str::isUuid($sessionId)) {
            throw ApiException::notFound('The MCP SSE session was not found.');
        }

        $sessionKey = "mcp:sse:session:$sessionId";
        $sessionUserId = Redis::get($sessionKey);

        if ((string) $sessionUserId !== (string) $request->user()->id) {
            throw ApiException::notFound('The MCP SSE session was not found.');
        }

        $response = ($this->mcp)($request);

        if ($request->input('id') !== null) {
            Redis::rpush("mcp:sse:queue:$sessionId", $response->getContent());
            Redis::expire($sessionKey, 3600);
        }

        return response('', 202);
    }
}
