<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GuildUnion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostUnionOptionsController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user && $user->is_active && ($user->hasPermission('posts.create') || $user->hasPermission('posts.edit')), 403);

        $query = GuildUnion::query();
        if (! $user->hasRole('super-admin')) {
            if ($user->union_id) {
                $query->whereKey($user->union_id);
            } elseif ($user->hasRole('union-expert')) {
                $query->whereKey(0);
            }
        }

        $data = $query->orderByDesc('is_active')->orderBy('title')->orderBy('name')
            ->get(['id', 'title', 'name', 'is_active'])
            ->map(fn (GuildUnion $union): array => [
                'id' => (string) $union->id,
                'text' => $union->display_title,
                'is_active' => (bool) $union->is_active,
            ])->values();

        return response()->json(['data' => $data])
            ->header('Cache-Control', 'private, no-store, max-age=0');
    }
}
