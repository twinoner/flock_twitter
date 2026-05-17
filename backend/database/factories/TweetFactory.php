<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TweetFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'body'    => fake()->realText(rand(40, 200)),
        ];
    }
}
