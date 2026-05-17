<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Tweet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    public function store(Request $request, Tweet $tweet): JsonResponse
    {
        Like::firstOrCreate([
            'user_id'  => $request->user()->id,
            'tweet_id' => $tweet->id,
        ]);

        return response()->json([
            'liked'       => true,
            'likes_count' => $tweet->likes()->count(),
        ]);
    }

    public function destroy(Request $request, Tweet $tweet): JsonResponse
    {
        Like::where('user_id', $request->user()->id)
            ->where('tweet_id', $tweet->id)
            ->delete();

        return response()->json([
            'liked'       => false,
            'likes_count' => $tweet->likes()->count(),
        ]);
    }
}
