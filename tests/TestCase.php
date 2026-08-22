<?php

namespace Tests;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        $environment = $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? getenv('APP_ENV');
        $database = $_ENV['DB_DATABASE'] ?? $_SERVER['DB_DATABASE'] ?? getenv('DB_DATABASE');

        if ($environment !== 'testing') {
            throw new RuntimeException('Tests may only run with APP_ENV=testing.');
        }

        if ($database === 'aliche_asnaf2') {
            throw new RuntimeException('Refusing to run tests against the production database.');
        }

        parent::setUp();

        if (! app()->environment('testing') || config('database.connections.'.config('database.default').'.database') === 'aliche_asnaf2') {
            throw new RuntimeException('Unsafe test database configuration detected.');
        }
    }

    protected function signInAsSuperAdmin(): User
    {
        $role = Role::query()->firstOrCreate(
            ['name' => 'super-admin'],
            ['label' => 'مدیر کل']
        );
        $user = User::factory()->create(['is_active' => true]);
        $user->roles()->syncWithoutDetaching([$role->id]);

        $this->actingAs($user);

        return $user;
    }
}
