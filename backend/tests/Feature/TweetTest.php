<?php

namespace Tests\Feature;

use App\Models\Tweet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TweetTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsUser(): array
    {
        $user  = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;
        return [$user, $token];
    }

    public function test_authenticated_user_can_create_tweet(): void
    {
        [$user, $token] = $this->actingAsUser();

        $response = $this->withToken($token)->postJson('/api/tweets', [
            'body' => 'Hello world!',
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'id', 'body', 'user_id', 'created_at',
                     'user', 'likes_count', 'liked_by_auth_user',
                 ]);

        $this->assertDatabaseHas('tweets', [
            'body'    => 'Hello world!',
            'user_id' => $user->id,
        ]);
    }

    public function test_tweet_body_cannot_exceed_280_chars(): void
    {
        [, $token] = $this->actingAsUser();

        $response = $this->withToken($token)->postJson('/api/tweets', [
            'body' => str_repeat('a', 281),
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['body']);
    }

    public function test_tweet_body_cannot_be_empty(): void
    {
        [, $token] = $this->actingAsUser();

        $response = $this->withToken($token)->postJson('/api/tweets', [
            'body' => '',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['body']);
    }

    public function test_unauthenticated_user_cannot_create_tweet(): void
    {
        $this->postJson('/api/tweets', ['body' => 'Hello'])
             ->assertStatus(401);
    }

    public function test_user_can_delete_own_tweet(): void
    {
        [$user, $token] = $this->actingAsUser();
        $tweet = Tweet::factory()->create(['user_id' => $user->id]);

        $response = $this->withToken($token)->deleteJson("/api/tweets/{$tweet->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('tweets', ['id' => $tweet->id]);
    }

    public function test_user_cannot_delete_another_users_tweet(): void
    {
        [, $token]   = $this->actingAsUser();
        $other        = User::factory()->create();
        $tweet        = Tweet::factory()->create(['user_id' => $other->id]);

        $response = $this->withToken($token)->deleteJson("/api/tweets/{$tweet->id}");

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_delete_tweet(): void
    {
        $tweet = Tweet::factory()->create();

        $this->deleteJson("/api/tweets/{$tweet->id}")
             ->assertStatus(401);
    }
}
