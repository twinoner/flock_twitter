<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    public function store(Request $request, User $user): JsonResponse
    {
        if ($request->user()->id === $user->id) {
            return response()->json(['message' => 'Cannot follow yourself'], 422);
        }

        $request->user()->following()->syncWithoutDetaching([$user->id]);

        return response()->json(['following' => true]);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        $request->user()->following()->detach($user->id);

        return response()->json(['following' => false]);
    }
}
