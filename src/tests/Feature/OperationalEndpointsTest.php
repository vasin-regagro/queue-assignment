<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\ServiceQueue;
use App\Models\User;
use App\Services\JwtService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Redis;
use Mockery;
use Tests\TestCase;

class OperationalEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_reports_database_and_redis_status(): void
    {
        $connection = Mockery::mock();
        $connection->shouldReceive('ping')->once()->andReturn(true);
        Redis::shouldReceive('connection')->once()->andReturn($connection);

        $this->getJson('/api/health')
            ->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('checks.database', true)
            ->assertJsonPath('checks.redis', true);
    }

    public function test_health_degrades_when_redis_is_unavailable(): void
    {
        Redis::shouldReceive('connection')->once()->andThrow(new \RuntimeException('Redis unavailable'));

        $this->getJson('/api/health')
            ->assertStatus(503)
            ->assertJsonPath('status', 'degraded')
            ->assertJsonPath('checks.database', true)
            ->assertJsonPath('checks.redis', false);
    }

    public function test_metrics_require_administrator_and_include_domain_counts(): void
    {
        $user = User::factory()->create();

        $this->actingAsApi($user)
            ->getJson('/api/metrics')
            ->assertForbidden();

        $administrator = User::factory()->create([
            'roles' => [UserRole::USER->value, UserRole::ADMINISTRATOR->value],
        ]);
        ServiceQueue::create([
            'name' => 'Metrics queue',
            'status' => 'OPEN',
            'next_ticket_number' => 1,
        ]);

        Redis::shouldReceive('get')->times(5)->andReturn('3');

        $this->actingAsApi($administrator)
            ->getJson('/api/metrics')
            ->assertOk()
            ->assertJsonPath('data.transport.http_requests_total', 3)
            ->assertJsonPath('data.domain.queues', 1)
            ->assertJsonPath('data.domain.activeEntries', 0)
            ->assertJsonPath('data.domain.mcpFailures', 0);
    }

    public function test_unknown_api_route_and_wrong_method_use_standard_error_contract(): void
    {
        $this->getJson('/api/does-not-exist')
            ->assertNotFound()
            ->assertJsonPath('error.code', 'NOT_FOUND');

        $this->getJson('/api/auth/login')
            ->assertStatus(405)
            ->assertJsonPath('error.code', 'METHOD_NOT_ALLOWED');
    }

    private function actingAsApi(User $user): static
    {
        return $this->withHeader('Authorization', 'Bearer '.app(JwtService::class)->issue($user));
    }
}
