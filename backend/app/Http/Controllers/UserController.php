<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function show(string $username): JsonResponse
    {
        $user = User::where('username', $username)
            ->withCount(['followers', 'following'])
            ->firstOrFail();

        return response()->json([
            'id'              => $user->id,
            'name'            => $user->name,
            'username'        => $user->username,
            'bio'             => $user->bio,
            'avatar'          => $user->avatar,
            'followers_count' => $user->followers_count,
            'following_count' => $user->following_count,
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

    public function followers(string $username): JsonResponse
    {
        $user      = User::where('username', $username)->firstOrFail();
        $followers = $user->followers()
            ->get(['users.id', 'users.name', 'users.username', 'users.avatar']);

        return response()->json(['data' => $followers]);
    }

    public function following(string $username): JsonResponse
    {
        $user      = User::where('username', $username)->firstOrFail();
        $following = $user->following()
            ->get(['users.id', 'users.name', 'users.username', 'users.avatar']);

        return response()->json(['data' => $following]);
    }
}
