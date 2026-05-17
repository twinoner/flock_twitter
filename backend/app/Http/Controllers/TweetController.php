<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTweetRequest;
use App\Models\Tweet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TweetController extends Controller
{
    public function store(StoreTweetRequest $request): JsonResponse
    {
        $tweet = $request->user()->tweets()->create($request->validated());

        $tweet->load('user');
        $tweet->setAttribute('likes_count', 0);
        $tweet->setAttribute('liked_by_auth_user', false);

        return response()->json($tweet, 201);
    }

    public function destroy(Request $request, Tweet $tweet): JsonResponse
    {
        if ($tweet->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $tweet->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
