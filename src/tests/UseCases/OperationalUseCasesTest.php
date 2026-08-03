<?php

namespace Tests\UseCases;

use App\Enums\QueueEntryStatus;
use App\Models\User;
use Illuminate\Support\Facades\Redis;

class OperationalUseCasesTest extends UseCaseTestCase
{
    public function test_uc_013_observation_signals_start_a_new_business_cycle(): void
    {
        $this->markTestIncomplete(
            'The stage 10 observation store and its automated hand-off to stage 1 are not implemented.',
        );
    }

    public function test_uc_014_container_deployment_contract_and_health_endpoint_are_present(): void
    {
        $root = base_path('..');
        $compose = file_get_contents($root.'/docker-compose.yml');

        $this->assertFileExists($root.'/docker-compose.yml');
        $this->assertFileExists(base_path('docker/php/Dockerfile'));
        $this->assertFileExists(base_path('docker/nginx/Dockerfile'));
        $this->assertFileExists(base_path('docker/nginx/default.conf'));
        $this->assertStringContainsString('app:', $compose);
        $this->assertStringContainsString('nginx:', $compose);
        $this->assertStringContainsString('db:', $compose);
        $this->assertStringContainsString('redis:', $compose);
        $this->assertStringContainsString('healthcheck:', $compose);
        $this->assertStringContainsString('restart: unless-stopped', $compose);

        $connection = \Mockery::mock();
        $connection->shouldReceive('ping')->once()->andReturn('PONG');
        Redis::shouldReceive('connection')->once()->andReturn($connection);

        $this->getJson('/api/health')
            ->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('checks.database', true)
            ->assertJsonPath('checks.redis', true);
    }

    public function test_uc_015_acceptance_report_controls_the_release_gate(): void
    {
        $this->markTestIncomplete(
            'AcceptanceReport generation and the automated evaluation release gate are not implemented.',
        );
    }

    public function test_uc_016_public_board_displays_only_active_queue_data_without_authentication(): void
    {
        $queue = $this->queue(name: 'UC 016 Public Queue');
        $active = User::factory()->create([
            'name' => 'Visible Queue User',
            'email' => 'private@example.test',
        ]);
        $completed = User::factory()->create(['name' => 'Hidden Completed User']);
        $this->entry($queue, $active, 1);
        $this->entry($queue, $completed, 2, QueueEntryStatus::COMPLETED);

        $this->get('/queues-board')
            ->assertOk()
            ->assertSee('UC 016 Public Queue')
            ->assertSee('Visible Queue User')
            ->assertSee('Ожидает')
            ->assertSee('http-equiv="refresh"', false)
            ->assertDontSee('Hidden Completed User')
            ->assertDontSee('private@example.test');
    }
}
