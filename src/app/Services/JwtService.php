<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\User;
use JsonException;

class JwtService
{
    public function issue(User $user): string
    {
        $now = now()->timestamp;
        $ttl = $user->is_guest ? config('jwt.guest_ttl') : config('jwt.ttl');

        return $this->encode([
            'iss' => config('jwt.issuer'),
            'sub' => (string) $user->getKey(),
            'iat' => $now,
            'exp' => $now + $ttl,
            'roles' => $user->roles,
        ]);
    }

    /** @return array<string, mixed> */
    public function decode(string $token): array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            throw ApiException::unauthorized('The JWT format is invalid.');
        }

        [$header, $payload, $signature] = $parts;
        $expected = $this->base64UrlEncode(hash_hmac('sha256', "$header.$payload", $this->secret(), true));

        if (! hash_equals($expected, $signature)) {
            throw ApiException::unauthorized('The JWT signature is invalid.');
        }

        try {
            $claims = json_decode($this->base64UrlDecode($payload), true, 32, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw ApiException::unauthorized('The JWT payload is invalid.');
        }

        if (! is_array($claims) || ($claims['iss'] ?? null) !== config('jwt.issuer')) {
            throw ApiException::unauthorized('The JWT issuer is invalid.');
        }

        if (! isset($claims['exp']) || (int) $claims['exp'] <= now()->timestamp) {
            throw ApiException::unauthorized('The JWT has expired.');
        }

        return $claims;
    }

    /** @param array<string, mixed> $claims */
    private function encode(array $claims): string
    {
        $header = $this->base64UrlEncode(json_encode(['alg' => 'HS256', 'typ' => 'JWT'], JSON_THROW_ON_ERROR));
        $payload = $this->base64UrlEncode(json_encode($claims, JSON_THROW_ON_ERROR));
        $signature = $this->base64UrlEncode(hash_hmac('sha256', "$header.$payload", $this->secret(), true));

        return "$header.$payload.$signature";
    }

    private function secret(): string
    {
        $secret = (string) config('jwt.secret');

        if (str_starts_with($secret, 'base64:')) {
            $decoded = base64_decode(substr($secret, 7), true);
            $secret = $decoded === false ? '' : $decoded;
        }

        if (strlen($secret) < 32) {
            throw new \RuntimeException('JWT_SECRET must contain at least 32 bytes.');
        }

        return $secret;
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $value): string
    {
        $padding = (4 - strlen($value) % 4) % 4;
        $decoded = base64_decode(strtr($value.str_repeat('=', $padding), '-_', '+/'), true);

        if ($decoded === false) {
            throw ApiException::unauthorized('The JWT encoding is invalid.');
        }

        return $decoded;
    }
}
