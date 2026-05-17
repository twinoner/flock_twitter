<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Tweet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TimelineController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $limit  = max(1, min((int) $request->query('limit', 20), 50));
        $cursor = $request->query('cursor');

        $followingIds = $request->user()->following()->pluck('users.id');

        $query = Tweet::with('user')
            ->withCount('likes')
            ->whereIn('user_id', $followingIds)
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        if ($cursor) {
            $decoded = base64_decode(strtr($cursor, '-_', '+/'));
            if ($decoded && str_contains($decoded, '_')) {
                [$cursorDate, $cursorId] = explode('_', $decoded, 2);
                $query->where(function ($q) use ($cursorDate, $cursorId) {
                    $q->where('created_at', '<', $cursorDate)
                      ->orWhere(function ($q2) use ($cursorDate, $cursorId) {
                          $q2->where('created_at', $cursorDate)
                             ->where('id', '<', (int) $cursorId);
                      });
                });
            }
        }

        // Fetch one extra to know if there's a next page
        $tweets  = $query->limit($limit + 1)->get();
        $hasMore = $tweets->count() > $limit;
        $tweets  = $tweets->take($limit);

        // Batch-check which tweets the auth user has liked
        $authUserId = $request->user()->id;
        $tweetIds   = $tweets->pluck('id');
        $likedIds   = Like::where('user_id', $authUserId)
            ->whereIn('tweet_id', $tweetIds)
            ->pluck('tweet_id')
            ->flip();

        $tweets->each(function ($tweet) use ($likedIds) {
            $tweet->setAttribute('liked_by_auth_user', $likedIds->has($tweet->id));
        });

        $nextCursor = null;
        if ($hasMore) {
            $last       = $tweets->last();
            $nextCursor = rtrim(strtr(base64_encode($last->created_at->format('Y-m-d H:i:s') . '_' . $last->id), '+/', '-_'), '=');
        }

        return response()->json([
            'data'        => $tweets->values(),
            'next_cursor' => $nextCursor,
        ]);
    }
}
