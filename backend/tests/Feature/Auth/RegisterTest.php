<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_with_valid_data(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name'                  => 'John Doe',
            'username'              => 'johndoe',
            'email'                 => 'john@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure(['token', 'user' => ['id', 'name', 'username', 'email']]);

        $this->assertDatabaseHas('users', ['email' => 'john@example.com', 'username' => 'johndoe']);
    }

    public function test_register_fails_with_duplicate_email(): void
    {
        \App\Models\User::factory()->create(['email' => 'john@example.com']);

        $response = $this->postJson('/api/auth/register', [
            'name'                  => 'Jane',
            'username'              => 'jane',
            'email'                 => 'john@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['email']);
    }

    public function test_register_fails_with_duplicate_username(): void
    {
        \App\Models\User::factory()->create(['username' => 'johndoe']);

        $response = $this->postJson('/api/auth/register', [
            'name'                  => 'Jane',
            'username'              => 'johndoe',
            'email'                 => 'jane@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['username']);
    }

    public function test_register_fails_when_username_exceeds_50_chars(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name'                  => 'John',
            'username'              => str_repeat('a', 51),
            'email'                 => 'john@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['username']);
    }
}
