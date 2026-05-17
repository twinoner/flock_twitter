<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FollowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_follow_another_user(): void
    {
        $auth   = User::factory()->create();
        $target = User::factory()->create();
        $token  = $auth->createToken('api')->plainTextToken;

        $response = $this->withToken($token)->postJson("/api/users/{$target->id}/follow");

        $response->assertStatus(200)->assertJson(['following' => true]);
        $this->assertTrue($auth->fresh()->isFollowing($target));
    }

    public function test_user_can_unfollow(): void
    {
        $auth   = User::factory()->create();
        $target = User::factory()->create();
        $auth->following()->attach($target->id);
        $token  = $auth->createToken('api')->plainTextToken;

        $response = $this->withToken($token)->deleteJson("/api/users/{$target->id}/follow");

        $response->assertStatus(200)->assertJson(['following' => false]);
        $this->assertFalse($auth->fresh()->isFollowing($target));
    }

    public function test_user_cannot_follow_themselves(): void
    {
        $auth  = User::factory()->create();
        $token = $auth->createToken('api')->plainTextToken;

        $response = $this->withToken($token)->postJson("/api/users/{$auth->id}/follow");

        $response->assertStatus(422);
    }

    public function test_following_same_user_twice_is_idempotent(): void
    {
        $auth   = User::factory()->create();
        $target = User::factory()->create();
        $auth->following()->attach($target->id);
        $token  = $auth->createToken('api')->plainTextToken;

        $response = $this->withToken($token)->postJson("/api/users/{$target->id}/follow");

        $response->assertStatus(200)->assertJson(['following' => true]);
        $this->assertCount(1, $auth->fresh()->following()->get());
    }

    public function test_unfollowing_someone_not_followed_is_idempotent(): void
    {
        $auth   = User::factory()->create();
        $target = User::factory()->create();
        $token  = $auth->createToken('api')->plainTextToken;

        $response = $this->withToken($token)->deleteJson("/api/users/{$target->id}/follow");

        $response->assertStatus(200)->assertJson(['following' => false]);
    }

    public function test_follow_requires_auth(): void
    {
        $target = User::factory()->create();
        $this->postJson("/api/users/{$target->id}/follow")->assertStatus(401);
    }

    public function test_follow_nonexistent_user_returns_404(): void
    {
        $auth  = User::factory()->create();
        $token = $auth->createToken('api')->plainTextToken;

        $this->withToken($token)->postJson('/api/users/99999/follow')->assertStatus(404);
    }
}
