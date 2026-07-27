<?php

namespace Tests\Unit;

use App\Exceptions\ApiException;
use App\Models\User;
use App\Services\JwtService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class JwtServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_issued_token_can_be_decoded(): void
    {
        $user = User::factory()->create();
        $claims = app(JwtService::class)->decode(app(JwtService::class)->issue($user));

        $this->assertSame((string) $user->id, $claims['sub']);
        $this->assertSame(config('jwt.issuer'), $claims['iss']);
        $this->assertSame($user->roles, $claims['roles']);
        $this->assertGreaterThan($claims['iat'], $claims['exp']);
    }

    public function test_invalid_format_and_signature_are_rejected(): void
    {
        $service = app(JwtService::class);

        try {
            $service->decode('not-a-jwt');
            $this->fail('Invalid JWT format was accepted.');
        } catch (ApiException $exception) {
            $this->assertSame('The JWT format is invalid.', $exception->getMessage());
        }

        $user = User::factory()->create();
        $token = $service->issue($user);
        $tampered = substr($token, 0, -1).(str_ends_with($token, 'a') ? 'b' : 'a');

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('The JWT signature is invalid.');
        $service->decode($tampered);
    }

    public function test_expired_token_is_rejected(): void
    {
        Carbon::setTestNow('2026-07-27 00:00:00');
        $user = User::factory()->create();
        $token = app(JwtService::class)->issue($user);
        Carbon::setTestNow(now()->addSeconds((int) config('jwt.ttl') + 1));

        try {
            app(JwtService::class)->decode($token);
            $this->fail('Expired JWT was accepted.');
        } catch (ApiException $exception) {
            $this->assertSame('The JWT has expired.', $exception->getMessage());
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_short_secret_is_rejected(): void
    {
        config(['jwt.secret' => 'too-short']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('JWT_SECRET must contain at least 32 bytes.');

        app(JwtService::class)->issue(User::factory()->create());
    }
}
