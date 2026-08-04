<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Services\SlugRedirectService;
use Illuminate\View\View;

class VideoController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));

        $videos = Video::query()
            ->published()
            ->with('union')
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")))
            ->orderBy('sort_order')
            ->latest('published_at')
            ->paginate(12)
            ->withQueryString();

        return view('frontend.videos.index', compact('videos', 'search'));
    }

    public function show(string $slug): View|RedirectResponse
    {
        if ($redirect = app(SlugRedirectService::class)->redirectIfLegacy(Video::class, $slug, 'videos.show')) {
            return $redirect;
        }

        $video = Video::query()
            ->published()
            ->with('union')
            ->where('slug', $slug)
            ->firstOrFail();

        $relatedVideos = Video::query()
            ->published()
            ->whereKeyNot($video->id)
            ->latest('published_at')
            ->take(5)
            ->get();

        return view('frontend.videos.show', compact('video', 'relatedVideos'));
    }
}
