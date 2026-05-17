<?php

namespace Tests\Feature;

use App\Models\Like;
use App\Models\Tweet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LikeTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_like_a_tweet(): void
    {
        $auth  = User::factory()->create();
        $tweet = Tweet::factory()->create();
        $token = $auth->createToken('api')->plainTextToken;

        $response = $this->withToken($token)->postJson("/api/tweets/{$tweet->id}/like");

        $response->assertStatus(200)
                 ->assertJson(['liked' => true])
                 ->assertJsonStructure(['liked', 'likes_count']);

        $this->assertDatabaseHas('likes', [
            'user_id'  => $auth->id,
            'tweet_id' => $tweet->id,
        ]);
    }

    public function test_user_can_unlike_a_tweet(): void
    {
        $auth  = User::factory()->create();
        $tweet = Tweet::factory()->create();
        Like::create(['user_id' => $auth->id, 'tweet_id' => $tweet->id]);
        $token = $auth->createToken('api')->plainTextToken;

        $response = $this->withToken($token)->deleteJson("/api/tweets/{$tweet->id}/like");

        $response->assertStatus(200)
                 ->assertJson(['liked' => false, 'likes_count' => 0]);

        $this->assertDatabaseMissing('likes', [
            'user_id'  => $auth->id,
            'tweet_id' => $tweet->id,
        ]);
    }

    public function test_liking_same_tweet_twice_is_idempotent(): void
    {
        $auth  = User::factory()->create();
        $tweet = Tweet::factory()->create();
        Like::create(['user_id' => $auth->id, 'tweet_id' => $tweet->id]);
        $token = $auth->createToken('api')->plainTextToken;

        $response = $this->withToken($token)->postJson("/api/tweets/{$tweet->id}/like");

        $response->assertStatus(200)->assertJson(['liked' => true]);
        $this->assertDatabaseCount('likes', 1);
    }

    public function test_likes_count_is_correct_after_like(): void
    {
        $auth   = User::factory()->create();
        $other  = User::factory()->create();
        $tweet  = Tweet::factory()->create();
        Like::create(['user_id' => $other->id, 'tweet_id' => $tweet->id]);
        $token  = $auth->createToken('api')->plainTextToken;

        $response = $this->withToken($token)->postJson("/api/tweets/{$tweet->id}/like");

        $response->assertStatus(200)->assertJson(['likes_count' => 2]);
    }

    public function test_like_requires_auth(): void
    {
        $tweet = Tweet::factory()->create();
        $this->postJson("/api/tweets/{$tweet->id}/like")->assertStatus(401);
    }

    public function test_unlike_requires_auth(): void
    {
        $tweet = Tweet::factory()->create();
        $this->deleteJson("/api/tweets/{$tweet->id}/like")->assertStatus(401);
    }

    public function test_unliking_a_tweet_never_liked_returns_200(): void
    {
        $auth  = User::factory()->create();
        $tweet = Tweet::factory()->create();
        $token = $auth->createToken('api')->plainTextToken;

        $this->withToken($token)->deleteJson("/api/tweets/{$tweet->id}/like")
             ->assertStatus(200)
             ->assertJson(['liked' => false, 'likes_count' => 0]);
    }

    public function test_like_nonexistent_tweet_returns_404(): void
    {
        $auth  = User::factory()->create();
        $token = $auth->createToken('api')->plainTextToken;

        $this->withToken($token)->postJson('/api/tweets/99999/like')->assertStatus(404);
    }
}
