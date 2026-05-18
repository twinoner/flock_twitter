<?php

namespace Tests\Feature;

use App\Models\Tweet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserProfileTest extends TestCase
{
    use RefreshDatabase;

    // ─── show() is_following ────────────────────────────────────────────────

    public function test_profile_includes_is_following_false_for_guest(): void
    {
        User::factory()->create(['username' => 'alice']);

        $this->getJson('/api/users/alice')
             ->assertStatus(200)
             ->assertJson(['is_following' => false]);
    }

    public function test_profile_is_following_false_when_not_following(): void
    {
        $auth   = User::factory()->create();
        $target = User::factory()->create(['username' => 'bob']);
        $token  = $auth->createToken('api')->plainTextToken;

        $this->withToken($token)->getJson('/api/users/bob')
             ->assertStatus(200)
             ->assertJson(['is_following' => false]);
    }

    public function test_profile_is_following_true_when_following(): void
    {
        $auth   = User::factory()->create();
        $target = User::factory()->create(['username' => 'bob']);
        $auth->following()->attach($target->id);
        $token  = $auth->createToken('api')->plainTextToken;

        $this->withToken($token)->getJson('/api/users/bob')
             ->assertStatus(200)
             ->assertJson(['is_following' => true]);
    }

    public function test_profile_is_following_always_false_for_own_profile(): void
    {
        $auth  = User::factory()->create(['username' => 'alice']);
        $token = $auth->createToken('api')->plainTextToken;

        $this->withToken($token)->getJson('/api/users/alice')
             ->assertStatus(200)
             ->assertJson(['is_following' => false]);
    }

    // ─── followers() is_following ────────────────────────────────────────────

    public function test_followers_list_marks_users_auth_is_following(): void
    {
        $profile  = User::factory()->create(['username' => 'alice']);
        $followerA = User::factory()->create();
        $followerB = User::factory()->create();

        // Both follow alice
        $followerA->following()->attach($profile->id);
        $followerB->following()->attach($profile->id);

        $auth  = User::factory()->create();
        // Auth follows followerA but not followerB
        $auth->following()->attach($followerA->id);
        $token = $auth->createToken('api')->plainTextToken;

        $data = $this->withToken($token)
                     ->getJson('/api/users/alice/followers')
                     ->assertStatus(200)
                     ->json('data');

        $byId = collect($data)->keyBy('id');
        $this->assertTrue($byId[$followerA->id]['is_following']);
        $this->assertFalse($byId[$followerB->id]['is_following']);
    }

    public function test_followers_list_is_following_false_for_all_when_guest(): void
    {
        $profile  = User::factory()->create(['username' => 'alice']);
        $follower = User::factory()->create();
        $follower->following()->attach($profile->id);

        $auth  = User::factory()->create();
        $token = $auth->createToken('api')->plainTextToken;

        $data = $this->withToken($token)
                     ->getJson('/api/users/alice/followers')
                     ->assertStatus(200)
                     ->json('data');

        $this->assertFalse($data[0]['is_following']);
    }

    // ─── following() is_following ────────────────────────────────────────────

    public function test_following_list_marks_users_auth_is_following(): void
    {
        $profile   = User::factory()->create(['username' => 'alice']);
        $followedA = User::factory()->create();
        $followedB = User::factory()->create();

        // Alice follows both
        $profile->following()->attach([$followedA->id, $followedB->id]);

        $auth  = User::factory()->create();
        // Auth follows followedA but not followedB
        $auth->following()->attach($followedA->id);
        $token = $auth->createToken('api')->plainTextToken;

        $data = $this->withToken($token)
                     ->getJson('/api/users/alice/following')
                     ->assertStatus(200)
                     ->json('data');

        $byId = collect($data)->keyBy('id');
        $this->assertTrue($byId[$followedA->id]['is_following']);
        $this->assertFalse($byId[$followedB->id]['is_following']);
    }

    // ─── tweets() ────────────────────────────────────────────────────────────

    public function test_user_tweets_returns_only_that_users_tweets(): void
    {
        $alice = User::factory()->create(['username' => 'alice']);
        $bob   = User::factory()->create();
        Tweet::factory()->for($alice)->create(['body' => 'alice tweet']);
        Tweet::factory()->for($bob)->create(['body' => 'bob tweet']);

        $auth  = User::factory()->create();
        $token = $auth->createToken('api')->plainTextToken;

        $data = $this->withToken($token)
                     ->getJson('/api/users/alice/tweets')
                     ->assertStatus(200)
                     ->json('data');

        $this->assertCount(1, $data);
        $this->assertEquals('alice tweet', $data[0]['body']);
    }

    public function test_user_tweets_ordered_newest_first(): void
    {
        $alice = User::factory()->create(['username' => 'alice']);
        $old   = Tweet::factory()->for($alice)->create(['created_at' => now()->subDays(2)]);
        $new   = Tweet::factory()->for($alice)->create(['created_at' => now()]);

        $auth  = User::factory()->create();
        $token = $auth->createToken('api')->plainTextToken;

        $data = $this->withToken($token)
                     ->getJson('/api/users/alice/tweets')
                     ->assertStatus(200)
                     ->json('data');

        $this->assertEquals($new->id, $data[0]['id']);
        $this->assertEquals($old->id, $data[1]['id']);
    }

    public function test_user_tweets_includes_required_fields(): void
    {
        $alice = User::factory()->create(['username' => 'alice']);
        Tweet::factory()->for($alice)->create();

        $auth  = User::factory()->create();
        $token = $auth->createToken('api')->plainTextToken;

        $this->withToken($token)
             ->getJson('/api/users/alice/tweets')
             ->assertStatus(200)
             ->assertJsonStructure(['data' => [['id', 'body', 'created_at', 'likes_count', 'liked_by_auth_user', 'user']]]);
    }

    public function test_user_tweets_liked_by_auth_user_is_true_when_liked(): void
    {
        $alice = User::factory()->create(['username' => 'alice']);
        $tweet = Tweet::factory()->for($alice)->create();

        $auth  = User::factory()->create();
        $auth->likes()->create(['tweet_id' => $tweet->id]);
        $token = $auth->createToken('api')->plainTextToken;

        $data = $this->withToken($token)
                     ->getJson('/api/users/alice/tweets')
                     ->assertStatus(200)
                     ->json('data');

        $this->assertTrue($data[0]['liked_by_auth_user']);
    }

    public function test_user_tweets_liked_by_auth_user_is_false_when_not_liked(): void
    {
        $alice = User::factory()->create(['username' => 'alice']);
        Tweet::factory()->for($alice)->create();

        $auth  = User::factory()->create();
        $token = $auth->createToken('api')->plainTextToken;

        $data = $this->withToken($token)
                     ->getJson('/api/users/alice/tweets')
                     ->assertStatus(200)
                     ->json('data');

        $this->assertFalse($data[0]['liked_by_auth_user']);
    }

    public function test_user_tweets_returns_empty_for_user_with_no_tweets(): void
    {
        User::factory()->create(['username' => 'alice']);

        $auth  = User::factory()->create();
        $token = $auth->createToken('api')->plainTextToken;

        $this->withToken($token)
             ->getJson('/api/users/alice/tweets')
             ->assertStatus(200)
             ->assertJson(['data' => []]);
    }

    public function test_user_tweets_returns_404_for_unknown_username(): void
    {
        $auth  = User::factory()->create();
        $token = $auth->createToken('api')->plainTextToken;

        $this->withToken($token)
             ->getJson('/api/users/nobody/tweets')
             ->assertStatus(404);
    }

    public function test_user_tweets_requires_auth(): void
    {
        User::factory()->create(['username' => 'alice']);

        $this->getJson('/api/users/alice/tweets')->assertStatus(401);
    }
}
