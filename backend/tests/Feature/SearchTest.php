<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_search_users_by_name(): void
    {
        User::factory()->create(['name' => 'Alice Smith', 'username' => 'alice']);
        User::factory()->create(['name' => 'Bob Jones',   'username' => 'bob']);

        $response = $this->getJson('/api/users/search?q=Alice');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('alice', $data[0]['username']);
    }

    public function test_can_search_users_by_username(): void
    {
        User::factory()->create(['name' => 'Alice Smith', 'username' => 'alicesmith']);
        User::factory()->create(['name' => 'Bob Jones',   'username' => 'bobjones']);

        $response = $this->getJson('/api/users/search?q=bobjones');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('bobjones', $data[0]['username']);
    }

    public function test_search_is_case_insensitive(): void
    {
        User::factory()->create(['name' => 'Alice Smith', 'username' => 'alice']);

        $response = $this->getJson('/api/users/search?q=ALICE');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    public function test_search_returns_empty_when_no_match(): void
    {
        $response = $this->getJson('/api/users/search?q=nonexistent');

        $response->assertStatus(200)->assertJson(['data' => []]);
    }

    public function test_can_get_user_profile_by_username(): void
    {
        $user = User::factory()->create(['username' => 'alice']);

        $response = $this->getJson('/api/users/alice');

        $response->assertStatus(200)
                 ->assertJsonStructure(['id', 'name', 'username', 'bio', 'followers_count', 'following_count']);
    }

    public function test_profile_returns_404_for_unknown_username(): void
    {
        $this->getJson('/api/users/nobody')->assertStatus(404);
    }

    public function test_can_get_followers_list(): void
    {
        $user     = User::factory()->create(['username' => 'alice']);
        $follower = User::factory()->create();
        $follower->following()->attach($user->id);

        $auth  = User::factory()->create();
        $token = $auth->createToken('api')->plainTextToken;

        $response = $this->withToken($token)->getJson('/api/users/alice/followers');

        $response->assertStatus(200)->assertJsonCount(1, 'data');
    }

    public function test_can_get_following_list(): void
    {
        $user     = User::factory()->create(['username' => 'alice']);
        $followed = User::factory()->create();
        $user->following()->attach($followed->id);

        $auth  = User::factory()->create();
        $token = $auth->createToken('api')->plainTextToken;

        $response = $this->withToken($token)->getJson('/api/users/alice/following');

        $response->assertStatus(200)->assertJsonCount(1, 'data');
    }

    public function test_search_returns_empty_for_blank_query(): void
    {
        User::factory()->count(3)->create();

        $response = $this->getJson('/api/users/search?q=');

        $response->assertStatus(200)->assertJson(['data' => []]);
    }

    public function test_followers_and_following_require_auth(): void
    {
        User::factory()->create(['username' => 'alice']);

        $this->getJson('/api/users/alice/followers')->assertStatus(401);
        $this->getJson('/api/users/alice/following')->assertStatus(401);
    }
}
