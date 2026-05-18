<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function show(string $username, Request $request): JsonResponse
    {
        $user = User::where('username', $username)
            ->withCount(['followers', 'following'])
            ->firstOrFail();

        $authUser    = Auth::guard('sanctum')->user();
        $isFollowing = $authUser && $authUser->id !== $user->id
            ? $authUser->isFollowing($user)
            : false;

        return response()->json([
            'id'              => $user->id,
            'name'            => $user->name,
            'username'        => $user->username,
            'bio'             => $user->bio,
            'avatar'          => $user->avatar,
            'followers_count' => $user->followers_count,
            'following_count' => $user->following_count,
            'is_following'    => $isFollowing,
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $q = trim(strtolower($request->query('q', '')));

        if ($q === '') {
            return response()->json(['data' => []]);
        }

        $users = User::whereRaw('LOWER(name) LIKE ?', ["%{$q}%"])
            ->orWhereRaw('LOWER(username) LIKE ?', ["%{$q}%"])
            ->limit(20)
            ->get(['id', 'name', 'username', 'avatar', 'bio']);

        return response()->json(['data' => $users]);
    }

    public function followers(string $username, Request $request): JsonResponse
    {
        $user    = User::where('username', $username)->firstOrFail();
        $authId  = $request->user()?->id;

        $authFollowingIds = $authId
            ? User::find($authId)->following()->pluck('users.id')->flip()
            : collect();

        $followers = $user->followers()
            ->get(['users.id', 'users.name', 'users.username', 'users.avatar'])
            ->map(fn ($u) => array_merge($u->toArray(), [
                'is_following' => $authFollowingIds->has($u->id),
            ]));

        return response()->json(['data' => $followers]);
    }

    public function following(string $username, Request $request): JsonResponse
    {
        $user    = User::where('username', $username)->firstOrFail();
        $authId  = $request->user()?->id;

        $authFollowingIds = $authId
            ? User::find($authId)->following()->pluck('users.id')->flip()
            : collect();

        $following = $user->following()
            ->get(['users.id', 'users.name', 'users.username', 'users.avatar'])
            ->map(fn ($u) => array_merge($u->toArray(), [
                'is_following' => $authFollowingIds->has($u->id),
            ]));

        return response()->json(['data' => $following]);
    }

    public function tweets(string $username, Request $request): JsonResponse
    {
        $user   = User::where('username', $username)->firstOrFail();
        $authId = $request->user()?->id;

        $tweets = $user->tweets()
            ->with('user:id,name,username,avatar')
            ->withCount('likes')
            ->when($authId, fn ($q) => $q->withExists(['likes as liked_by_auth_user' => fn ($q) => $q->where('user_id', $authId)]))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        return response()->json(['data' => $tweets]);
    }
}
