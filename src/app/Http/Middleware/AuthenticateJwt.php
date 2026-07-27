<?php

namespace App\Http\Middleware;

use App\Exceptions\ApiException;
use App\Models\User;
use App\Services\JwtService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateJwt
{
    public function __construct(private readonly JwtService $jwt) {}

    public function handle(Request $request, Closure $next): Response
    {
        $header = $request->header('Authorization', '');

        if (! preg_match('/^Bearer\s+(.+)$/i', $header, $matches)) {
            throw ApiException::unauthorized();
        }

        $claims = $this->jwt->decode($matches[1]);
        $user = User::find($claims['sub'] ?? null);

        if (! $user) {
            throw ApiException::unauthorized('The token subject no longer exists.');
        }

        if ($user->is_guest && $user->guest_expires_at?->isPast()) {
            throw ApiException::unauthorized('The guest session has expired.');
        }

        $request->setUserResolver(fn (): User => $user);

        return $next($request);
    }
}
