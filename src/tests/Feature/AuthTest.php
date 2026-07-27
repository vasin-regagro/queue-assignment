<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_login_and_guest_access(): void
    {
        $this->postJson('/api/auth/register', [
            'name' => 'Alice',
            'email' => 'alice@example.test',
            'password' => 'password-123',
        ])->assertCreated()
            ->assertJsonPath('data.user.roles.0', 'USER')
            ->assertJsonStructure(['data' => ['accessToken']]);

        $this->postJson('/api/auth/login', [
            'email' => 'alice@example.test',
            'password' => 'password-123',
        ])->assertOk()->assertJsonStructure(['data' => ['accessToken']]);

        $this->postJson('/api/auth/guest', ['name' => 'Visitor'])
            ->assertCreated()
            ->assertJsonPath('data.expiresIn', 86400)
            ->assertJsonPath('data.user.isGuest', true);
    }
}
