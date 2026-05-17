<?php

namespace Tests\Feature;

use App\Models\Tweet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimelineTest extends TestCase
{
    use RefreshDatabase;

    public function test_timeline_returns_tweets_from_followed_users(): void
    {
        $auth     = User::factory()->create();
        $followed = User::factory()->create();
        $other    = User::factory()->create();

        $auth->following()->attach($followed->id);

        Tweet::factory()->create(['user_id' => $followed->id, 'body' => 'Followed tweet']);
        Tweet::factory()->create(['user_id' => $other->id,    'body' => 'Other tweet']);

        $token    = $auth->createToken('api')->plainTextToken;
        $response = $this->withToken($token)->getJson('/api/timeline');

        $response->assertStatus(200)
                 ->assertJsonStructure(['data', 'next_cursor'])
                 ->assertJsonCount(1, 'data');

        $this->assertEquals('Followed tweet', $response->json('data.0.body'));
    }

    public function test_timeline_is_ordered_newest_first(): void
    {
        $auth     = User::factory()->create();
        $followed = User::factory()->create();
        $auth->following()->attach($followed->id);

        $old = Tweet::factory()->create([
            'user_id'    => $followed->id,
            'created_at' => now()->subHour(),
        ]);
        $new = Tweet::factory()->create([
            'user_id'    => $followed->id,
            'created_at' => now(),
        ]);

        $token    = $auth->createToken('api')->plainTextToken;
        $response = $this->withToken($token)->getJson('/api/timeline');

        $this->assertEquals($new->id, $response->json('data.0.id'));
        $this->assertEquals($old->id, $response->json('data.1.id'));
    }

    public function test_timeline_supports_cursor_pagination(): void
    {
        $auth     = User::factory()->create();
        $followed = User::factory()->create();
        $auth->following()->attach($followed->id);

        // Create 25 tweets spaced 1 minute apart so ordering is deterministic
        foreach (range(1, 25) as $i) {
            Tweet::factory()->create([
                'user_id'    => $followed->id,
                'created_at' => now()->subMinutes(26 - $i),
            ]);
        }

        $token = $auth->createToken('api')->plainTextToken;

        $first  = $this->withToken($token)->getJson('/api/timeline?limit=10');
        $first->assertStatus(200)->assertJsonCount(10, 'data');
        $cursor = $first->json('next_cursor');
        $this->assertNotNull($cursor);

        $second = $this->withToken($token)->getJson("/api/timeline?limit=10&cursor={$cursor}");
        $second->assertStatus(200)->assertJsonCount(10, 'data');

        // Pages must not overlap
        $firstIds  = collect($first->json('data'))->pluck('id')->sort()->values();
        $secondIds = collect($second->json('data'))->pluck('id')->sort()->values();
        $this->assertEmpty($firstIds->intersect($secondIds));
    }

    public function test_timeline_next_cursor_is_null_when_no_more_pages(): void
    {
        $auth     = User::factory()->create();
        $followed = User::factory()->create();
        $auth->following()->attach($followed->id);

        Tweet::factory()->count(3)->create(['user_id' => $followed->id]);

        $token    = $auth->createToken('api')->plainTextToken;
        $response = $this->withToken($token)->getJson('/api/timeline?limit=10');

        $response->assertStatus(200)->assertJsonCount(3, 'data');
        $this->assertNull($response->json('next_cursor'));
    }

    public function test_timeline_requires_auth(): void
    {
        $this->getJson('/api/timeline')->assertStatus(401);
    }

    public function test_timeline_tweet_includes_required_fields(): void
    {
        $auth     = User::factory()->create();
        $followed = User::factory()->create();
        $auth->following()->attach($followed->id);
        Tweet::factory()->create(['user_id' => $followed->id]);

        $token    = $auth->createToken('api')->plainTextToken;
        $response = $this->withToken($token)->getJson('/api/timeline');

        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'body', 'user_id', 'created_at', 'user', 'likes_count', 'liked_by_auth_user'],
            ],
        ]);
    }
}
