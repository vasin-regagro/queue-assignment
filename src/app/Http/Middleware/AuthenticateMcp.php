<?php

namespace App\Http\Middleware;

use App\Exceptions\ApiException;
use App\Models\McpAccessToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateMcp
{
    public function __construct(private readonly AuthenticateJwt $jwt) {}

    public function handle(Request $request, Closure $next): Response
    {
        $queryToken = $request->query('token');

        if (! is_string($queryToken) || $queryToken === '') {
            return $this->jwt->handle($request, $next);
        }

        $accessToken = McpAccessToken::query()
            ->with('user')
            ->where('token_hash', hash('sha256', $queryToken))
            ->whereNull('revoked_at')
            ->first();

        if (! $accessToken || ($accessToken->expires_at && $accessToken->expires_at->isPast())) {
            throw ApiException::unauthorized('The MCP access token is invalid or expired.');
        }

        if (! $accessToken->user || ($accessToken->user->is_guest && $accessToken->user->guest_expires_at?->isPast())) {
            throw ApiException::unauthorized('The MCP token subject is unavailable.');
        }

        $accessToken->forceFill(['last_used_at' => now()])->save();
        $request->setUserResolver(fn () => $accessToken->user);
        $request->attributes->set('mcp_access_token_id', $accessToken->id);

        return $next($request);
    }
}
