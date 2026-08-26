<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\GuildUnion;
use App\Models\Post;
use App\Services\SlugRedirectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $search = trim((string) $request->query('search'));
        $categoryId = $this->positiveInteger($request->query('category_id'));
        $unionId = $this->positiveInteger($request->query('union_id'));
        $date = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $request->query('date'))
            ? (string) $request->query('date')
            : null;

        $publishedNews = fn ($query) => $query
            ->published()
            ->editorial();

        $posts = Post::query()
            ->published()
            ->editorial()
            ->with(['category', 'union', 'galleries'])
            ->withCount('galleries')
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('title', 'like', "%{$search}%")
                ->orWhere('excerpt', 'like', "%{$search}%")
                ->orWhere('body', 'like', "%{$search}%")
                ->orWhere('meta_keywords', 'like', "%{$search}%")))
            ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
            ->when($unionId, fn ($query) => $query->where('union_id', $unionId))
            ->when($date, fn ($query) => $query->whereDate('published_at', $date))
            ->orderByDesc('published_at')
            ->paginate(12)
            ->withQueryString();

        $viewData = [
            'posts' => $posts,
            'search' => $search,
            'categoryId' => $categoryId,
            'unionId' => $unionId,
            'date' => $date,
            'hasActiveFilters' => $search !== '' || $categoryId || $unionId || $date,
        ];

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'html' => view('frontend.posts.partials.results', $viewData)->render(),
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'total' => $posts->total(),
                'from' => $posts->firstItem(),
                'to' => $posts->lastItem(),
                'url' => $request->fullUrl(),
            ]);
        }

        return view('frontend.posts.index', array_merge($viewData, [
            'unions' => GuildUnion::query()
                ->active()
                ->where(fn ($query) => $query
                    ->whereHas('posts', $publishedNews)
                    ->when($unionId, fn ($query) => $query->orWhere(
                        $query->getModel()->getQualifiedKeyName(),
                        $unionId
                    )))
                ->withCount(['posts as published_news_count' => $publishedNews])
                ->orderBy('title')
                ->orderBy('name')
                ->get(),
        ]));
    }

    private function positiveInteger(mixed $value): ?int
    {
        $value = filter_var($value, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);

        return $value === false ? null : $value;
    }

    public function legacy(string $slug): RedirectResponse
    {
        $post = ctype_digit($slug)
            ? Post::query()->published()->findOrFail((int) $slug)
            : Post::query()->published()->where('slug', $slug)->firstOrFail();

        return redirect()->route('posts.show', $post->slug, 301);
    }

    public function show(string $slug): View|RedirectResponse
    {
        if ($redirect = app(SlugRedirectService::class)->redirectIfLegacy(Post::class, $slug, 'posts.show')) {
            return $redirect;
        }

        $post = Post::query()
            ->published()
            ->where('slug', $slug)
            ->with(['category', 'union', 'author', 'galleries', 'featuredMedia', 'mediaGallery'])
            ->withCount('galleries')
            ->firstOrFail();

        $post->increment('views_count');
        $post->refresh();

        $latestPosts = Post::query()
            ->published()
            ->editorial()
            ->whereKeyNot($post->id)
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->take(15)
            ->get(['id', 'title', 'slug', 'published_at']);

        $previousPost = Post::query()
            ->published()
            ->editorial()
            ->where('published_at', '<', $post->published_at)
            ->orderByDesc('published_at')
            ->first();

        $nextPost = Post::query()
            ->published()
            ->editorial()
            ->where('published_at', '>', $post->published_at)
            ->orderBy('published_at')
            ->first();

        return view('frontend.posts.show', compact('post', 'latestPosts', 'previousPost', 'nextPost'));
    }
}
