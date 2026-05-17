<?php

namespace Database\Seeders;

use App\Models\Follow;
use App\Models\Like;
use App\Models\Tweet;
use App\Models\User;
use Illuminate\Database\Seeder;

class TwitterSeeder extends Seeder
{
    public function run(): void
    {
        // 10 named users with realistic profiles — all passwords are "password"
        $userData = [
            ['name' => 'Alice Chen',      'username' => 'alicechen',     'email' => 'alice@example.com',    'bio' => 'Software engineer & coffee lover ☕'],
            ['name' => 'Bob Martinez',    'username' => 'bobmartinez',   'email' => 'bob@example.com',      'bio' => 'DevOps practitioner. Dad of 2.'],
            ['name' => 'Clara Johnson',   'username' => 'claraj',        'email' => 'clara@example.com',    'bio' => 'Frontend developer. Design nerd.'],
            ['name' => 'David Kim',       'username' => 'davidkim',      'email' => 'david@example.com',    'bio' => 'ML researcher at Stanford.'],
            ['name' => 'Eva Torres',      'username' => 'evatorres',     'email' => 'eva@example.com',      'bio' => 'Open source maintainer. She/her.'],
            ['name' => 'Frank Osei',      'username' => 'frankosei',     'email' => 'frank@example.com',    'bio' => 'Backend dev. Loves Rust.'],
            ['name' => 'Grace Liu',       'username' => 'graceliu',      'email' => 'grace@example.com',    'bio' => 'Product manager turned engineer.'],
            ['name' => 'Hiro Tanaka',     'username' => 'hirotanaka',    'email' => 'hiro@example.com',     'bio' => 'Gaming + code. Tokyo 🗼'],
            ['name' => 'Isabelle Blanc',  'username' => 'isabelleblanc', 'email' => 'isabelle@example.com', 'bio' => 'Security researcher. CTF player.'],
            ['name' => 'Jake Williams',   'username' => 'jakewilliams',  'email' => 'jake@example.com',     'bio' => 'Startup founder. Building in public.'],
        ];

        $users = collect($userData)->map(fn ($data) =>
            User::factory()->create(array_merge($data, ['password' => 'password']))
        );

        // Realistic tweets — varied topics, realistic lengths
        $tweetSets = [
            [ // Alice Chen
                'Just shipped a new feature after 3 days of debugging. The bug was a missing semicolon. 😅',
                'Hot take: code reviews are the best form of documentation.',
                'Reminder that it\'s okay to step away from the screen and go for a walk.',
                'PostgreSQL window functions are genuinely life-changing. Why did I wait so long?',
            ],
            [ // Bob Martinez
                'Anyone else spend 2 hours on a bug that turned out to be a typo?',
                'Docker finally clicked for me. Why did it take so long?!',
                'Kubernetes is just Docker with existential dread.',
                'Pushed to prod on a Friday. It\'s fine. Everything is fine. 🔥',
                'The best infra is the infra you don\'t have to think about.',
            ],
            [ // Clara Johnson
                'The best code is the code you don\'t have to write.',
                'Design systems save lives. Fight me.',
                'Dark mode isn\'t a preference, it\'s a lifestyle.',
                'Tailwind just makes sense once it clicks.',
            ],
            [ // David Kim
                'Just discovered that the "temporary" fix I wrote 2 years ago is still in production.',
                'New paper on transformer attention is wild. Link in replies.',
                'GPU prices are still too high. I\'m not over it.',
                'Fine-tuning LLMs on a laptop is a spiritual experience.',
                'Data is the new oil and I\'m the refinery.',
            ],
            [ // Eva Torres
                'Writing tests first really does make you think about design before code.',
                'Just merged a 200-line PR. Felt like surgery.',
                'Open source maintainership is 10% coding and 90% email.',
                'Thank you to everyone who submitted a bug report with steps to reproduce. You\'re heroes.',
            ],
            [ // Frank Osei
                'Rust error messages are love letters from the compiler.',
                'Memory safety isn\'t a feature, it\'s a right.',
                'Pair programming session today was insanely productive. Recommend.',
                'The joy of seeing green tests after a big refactor. 💚',
            ],
            [ // Grace Liu
                'The best products come from engineers who talk to users.',
                'Roadmaps are predictions, not promises.',
                'Reminder: done is better than perfect.',
                '"Move fast and break things" aged poorly.',
                'Just sat in a 3-hour meeting that could have been a Slack message.',
            ],
            [ // Hiro Tanaka
                'Coffee ☕ + lo-fi music + good problem = best morning ever.',
                'Beat a raid boss at 2am. No regrets. Slightly tired.',
                'The Switch 2 is real and I am not okay.',
                'Coding and gaming have more in common than people think.',
            ],
            [ // Isabelle Blanc
                'Just open-sourced a small CLI tool I\'ve been using for a year. Link in bio.',
                'CTF challenge this weekend. Sleep is optional.',
                'The scariest vulnerabilities are the boring ones.',
                'Social engineering is still the most effective attack vector.',
                'Password managers. Use them. Please.',
            ],
            [ // Jake Williams
                'Shipped v1. It\'s rough. It\'s real. It\'s out there.',
                'Building in public is terrifying and I love it.',
                'Investor meeting went well. Or at least I think so.',
                'The best startup advice: talk to your users before building anything.',
            ],
        ];

        // Create tweets with realistic timestamps spread over the last 7 days
        foreach ($users as $index => $user) {
            $tweets = $tweetSets[$index];
            foreach ($tweets as $i => $body) {
                Tweet::factory()->create([
                    'user_id'    => $user->id,
                    'body'       => $body,
                    'created_at' => now()->subDays(rand(0, 6))->subHours(rand(0, 23))->subMinutes(rand(0, 59)),
                    'updated_at' => now()->subDays(rand(0, 6))->subHours(rand(0, 23)),
                ]);
            }
        }

        $allTweets = Tweet::all();

        // Cross-follows: each user follows 4–7 others (not themselves)
        foreach ($users as $user) {
            $targets = $users
                ->filter(fn ($u) => $u->id !== $user->id)
                ->shuffle()
                ->take(rand(4, 7));

            $user->following()->syncWithoutDetaching($targets->pluck('id')->toArray());
        }

        // Cross-likes: each user likes 5–10 tweets (not their own)
        foreach ($users as $user) {
            $candidates = $allTweets->filter(fn ($t) => $t->user_id !== $user->id);
            $toLike = $candidates->shuffle()->take(min(rand(5, 10), $candidates->count()));

            foreach ($toLike as $tweet) {
                Like::firstOrCreate([
                    'user_id'  => $user->id,
                    'tweet_id' => $tweet->id,
                ]);
            }
        }

        $this->command->info('✓ Seeded ' . $users->count() . ' users, ' . Tweet::count() . ' tweets, ' . Follow::count() . ' follows, ' . Like::count() . ' likes.');
    }
}
