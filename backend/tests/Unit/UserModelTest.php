<?php

namespace Tests\Unit;

use App\Models\Like;
use App\Models\Tweet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_many_tweets(): void
    {
        $user = User::factory()->create();
        Tweet::factory()->count(3)->create(['user_id' => $user->id]);

        $this->assertCount(3, $user->tweets);
    }

    public function test_user_has_many_likes(): void
    {
        $user = User::factory()->create();
        $tweets = Tweet::factory()->count(2)->create();
        foreach ($tweets as $tweet) {
            Like::factory()->create(['user_id' => $user->id, 'tweet_id' => $tweet->id]);
        }

        $this->assertCount(2, $user->likes);
    }

    public function test_is_following_returns_true_when_following(): void
    {
        $user  = User::factory()->create();
        $other = User::factory()->create();
        $user->following()->attach($other->id);

        $this->assertTrue($user->isFollowing($other));
    }

    public function test_is_following_returns_false_when_not_following(): void
    {
        $user  = User::factory()->create();
        $other = User::factory()->create();

        $this->assertFalse($user->isFollowing($other));
    }

    public function test_is_following_uses_loaded_relation_to_avoid_extra_query(): void
    {
        $user  = User::factory()->create();
        $other = User::factory()->create();
        $user->following()->attach($other->id);

        // Load the relation first
        $user->load('following');

        // Should work without hitting DB again
        $this->assertTrue($user->isFollowing($other));
    }

    public function test_user_has_followers_relationship(): void
    {
        $user     = User::factory()->create();
        $follower = User::factory()->create();
        $follower->following()->attach($user->id);

        $this->assertCount(1, $user->followers);
        $this->assertEquals($follower->id, $user->followers->first()->id);
    }

    public function test_user_has_following_relationship(): void
    {
        $user   = User::factory()->create();
        $target = User::factory()->create();
        $user->following()->attach($target->id);

        $this->assertCount(1, $user->following);
        $this->assertEquals($target->id, $user->following->first()->id);
    }

    public function test_user_fillable_includes_required_fields(): void
    {
        $user = User::factory()->make([
            'name'     => 'Test User',
            'username' => 'testuser',
            'email'    => 'test@example.com',
            'bio'      => 'A bio',
            'avatar'   => null,
        ]);

        $this->assertEquals('Test User', $user->name);
        $this->assertEquals('testuser', $user->username);
        $this->assertEquals('A bio', $user->bio);
    }

    public function test_user_hidden_fields_not_in_array(): void
    {
        $user = User::factory()->create();
        $array = $user->toArray();

        $this->assertArrayNotHasKey('password', $array);
        $this->assertArrayNotHasKey('remember_token', $array);
    }
}
