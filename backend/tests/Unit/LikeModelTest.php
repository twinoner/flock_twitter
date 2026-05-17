<?php

namespace Tests\Unit;

use App\Models\Like;
use App\Models\Tweet;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LikeModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_like_belongs_to_user(): void
    {
        $user  = User::factory()->create();
        $tweet = Tweet::factory()->create();
        $like  = Like::factory()->create(['user_id' => $user->id, 'tweet_id' => $tweet->id]);

        $this->assertEquals($user->id, $like->user->id);
        $this->assertInstanceOf(User::class, $like->user);
    }

    public function test_like_belongs_to_tweet(): void
    {
        $tweet = Tweet::factory()->create();
        $like  = Like::factory()->create(['tweet_id' => $tweet->id]);

        $this->assertEquals($tweet->id, $like->tweet->id);
        $this->assertInstanceOf(Tweet::class, $like->tweet);
    }

    public function test_like_has_created_at_but_no_updated_at(): void
    {
        $like = Like::factory()->create();
        // created_at is set by the DB default (useCurrent); refresh to retrieve it
        $like->refresh();

        $this->assertNotNull($like->created_at);
        $this->assertNull($like->updated_at);
    }

    public function test_duplicate_like_throws_unique_constraint(): void
    {
        $user  = User::factory()->create();
        $tweet = Tweet::factory()->create();
        Like::factory()->create(['user_id' => $user->id, 'tweet_id' => $tweet->id]);

        $this->expectException(QueryException::class);
        Like::factory()->create(['user_id' => $user->id, 'tweet_id' => $tweet->id]);
    }

    public function test_like_fillable_includes_user_id_and_tweet_id(): void
    {
        $like = new Like();
        $this->assertContains('user_id', $like->getFillable());
        $this->assertContains('tweet_id', $like->getFillable());
    }
}
