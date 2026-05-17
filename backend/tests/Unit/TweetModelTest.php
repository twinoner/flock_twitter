<?php

namespace Tests\Unit;

use App\Models\Like;
use App\Models\Tweet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TweetModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_tweet_belongs_to_user(): void
    {
        $user  = User::factory()->create();
        $tweet = Tweet::factory()->create(['user_id' => $user->id]);

        $this->assertEquals($user->id, $tweet->user->id);
        $this->assertInstanceOf(User::class, $tweet->user);
    }

    public function test_tweet_has_many_likes(): void
    {
        $tweet = Tweet::factory()->create();
        Like::factory()->count(3)->create(['tweet_id' => $tweet->id]);

        $this->assertCount(3, $tweet->likes);
    }

    public function test_tweet_fillable_is_body_only(): void
    {
        $this->assertContains('body', (new Tweet())->getFillable());
        $this->assertNotContains('user_id', (new Tweet())->getFillable());
    }

    public function test_tweet_has_timestamps(): void
    {
        $tweet = Tweet::factory()->create();

        $this->assertNotNull($tweet->created_at);
        $this->assertNotNull($tweet->updated_at);
    }

    public function test_tweet_body_is_cast_to_string(): void
    {
        $tweet = Tweet::factory()->create(['body' => 'Hello world']);

        $this->assertIsString($tweet->body);
    }
}
