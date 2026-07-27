<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\JwtService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_protected_routes_require_a_bearer_token_and_return_a_correlation_id(): void
    {
        $response = $this->getJson('/api/queues')
            ->assertUnauthorized()
            ->assertJsonPath('error.code', 'UNAUTHORIZED');

        $correlationId = $response->headers->get('X-Correlation-ID');

        $this->assertIsString($correlationId);
        $this->assertMatchesRegularExpression('/^[0-9a-f-]{36}$/', $correlationId);
        $response->assertJsonPath('error.correlationId', $correlationId);
    }

    public function test_valid_correlation_id_is_preserved_on_validation_errors(): void
    {
        $this->withHeader('X-Correlation-ID', 'client-request-123')
            ->postJson('/api/auth/register', [])
            ->assertUnprocessable()
            ->assertHeader('X-Correlation-ID', 'client-request-123')
            ->assertJsonPath('error.code', 'VALIDATION_ERROR')
            ->assertJsonPath('error.correlationId', 'client-request-123')
            ->assertJsonStructure([
                'error' => [
                    'details' => ['name', 'email', 'password'],
                ],
            ]);
    }

    public function test_login_rejects_invalid_credentials_and_registration_rejects_duplicate_email(): void
    {
        User::factory()->create([
            'email' => 'existing@example.test',
            'password' => 'correct-password',
        ]);

        $this->postJson('/api/auth/login', [
            'email' => 'existing@example.test',
            'password' => 'wrong-password',
        ])
            ->assertUnauthorized()
            ->assertJsonPath('error.code', 'UNAUTHORIZED');

        $this->postJson('/api/auth/register', [
            'name' => 'Duplicate',
            'email' => 'existing@example.test',
            'password' => 'password-123',
        ])
            ->assertUnprocessable()
            ->assertJsonStructure(['error' => ['details' => ['email']]]);
    }

    public function test_expired_guest_and_deleted_token_subject_are_rejected(): void
    {
        $guest = User::factory()->create([
            'email' => null,
            'is_guest' => true,
            'guest_expires_at' => now()->subMinute(),
        ]);
        $guestToken = app(JwtService::class)->issue($guest);

        $this->withHeader('Authorization', "Bearer $guestToken")
            ->getJson('/api/queues')
            ->assertUnauthorized()
            ->assertJsonPath('error.message', 'The guest session has expired.');

        $user = User::factory()->create();
        $token = app(JwtService::class)->issue($user);
        $user->delete();

        $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/queues')
            ->assertUnauthorized()
            ->assertJsonPath('error.message', 'The token subject no longer exists.');
    }
}
