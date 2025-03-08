<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Тест регистрации пользователя.
     */
    public function test_user_can_register()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Тестовый Пользователь',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201) // Ожидаем 201 Created
        ->assertJsonStructure([
            'user' => ['id', 'name', 'email'],
            'token'
        ]);

        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    /**
     * Тест авторизации пользователя.
     */
    public function test_user_can_login()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'user' => ['id', 'name', 'email'],
                'token'
            ]);
    }

    /**
     * Тест получения профиля пользователя.
     */
    public function test_user_can_get_profile()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user); // Имитация авторизации

        $response = $this->getJson('/api/user');

        $response->assertStatus(200)
            ->assertJson([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]);
    }

    /**
     * Тест выхода пользователя.
     */
    public function test_user_can_logout()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Выход выполнен']);
    }
}
