<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SanctumAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_receives_token(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Ana',
            'email' => 'ana@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertCreated()
            ->assertJsonStructure(['message', 'user' => ['id', 'name', 'email'], 'token', 'token_type'])
            ->assertJsonMissingPath('user.password');

        $this->assertDatabaseHas('users', ['email' => 'ana@example.com']);
        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_register_validates_input(): void
    {
        $this->postJson('/api/register', ['email' => 'no-es-email'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create(['password' => 'secret123']);

        $this->postJson('/api/login', ['email' => $user->email, 'password' => 'secret123'])
            ->assertOk()
            ->assertJsonStructure(['token', 'user']);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $user = User::factory()->create(['password' => 'secret123']);

        $this->postJson('/api/login', ['email' => $user->email, 'password' => 'incorrecta'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_protected_route_requires_token(): void
    {
        $this->getJson('/api/user')->assertUnauthorized();
        $this->postJson('/api/logout')->assertUnauthorized();
    }

    public function test_protected_route_works_with_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('email', $user->email);
    }

    public function test_logout_revokes_current_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)->postJson('/api/logout')->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 0);

        // Olvidar el usuario cacheado por el guard para simular una nueva petición
        $this->app['auth']->forgetGuards();

        $this->withToken($token)->getJson('/api/user')->assertUnauthorized();
    }
}
