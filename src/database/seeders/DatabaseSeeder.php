<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->firstOrCreate(
            ['email' => env('SEED_ADMIN_EMAIL', 'admin@example.test')],
            [
                'name' => 'Demo Administrator',
                'password' => env('SEED_ADMIN_PASSWORD', 'change-me-admin'),
                'roles' => [UserRole::USER->value, UserRole::ADMINISTRATOR->value],
                'is_guest' => false,
            ],
        );

        User::query()->firstOrCreate(
            ['email' => env('SEED_OPERATOR_EMAIL', 'operator@example.test')],
            [
                'name' => 'Demo Operator',
                'password' => env('SEED_OPERATOR_PASSWORD', 'change-me-operator'),
                'roles' => [UserRole::USER->value, UserRole::OPERATOR->value],
                'is_guest' => false,
            ],
        );

        $this->call(DefaultQueueSeeder::class);
    }
}
