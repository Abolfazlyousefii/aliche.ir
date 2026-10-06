<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\CongratulationMessage;
use App\Models\GuildUnion;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Services\SlugRedirectService;
use Illuminate\View\View;

class UnionController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $search = mb_substr(trim((string) $request->query('search')), 0, 100);
        $type = (string) $request->query('type', '');
        $typeLabels = GuildUnion::typeLabels();
        $type = array_key_exists($type, $typeLabels) ? $type : '';
        $categoryId = $this->positiveInteger($request->query('category_id'));

        $baseQuery = GuildUnion::query()
            ->active()
            ->with(['category', 'unionType'])
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('title', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%")
                ->orWhere('manager_name', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('mobile', 'like', "%{$search}%")
                ->orWhere('short_description', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")))
            ->when($type !== '', fn ($query) => $query->where(function ($query) use ($type) {
                $query->where('union_type', $type)
                    ->orWhereHas('unionType', fn ($typeQuery) => $typeQuery->where('slug', $type));
            }))
            ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId));

        $unions = (clone $baseQuery)
            ->orderBy('sort_order')
            ->orderBy('title')
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        $countRows = GuildUnion::query()
            ->where('unions.is_active', true)
            ->leftJoin('union_types', 'union_types.id', '=', 'unions.union_type_id')
            ->selectRaw('COALESCE(union_types.slug, unions.union_type) as type_slug, COUNT(*) as aggregate')
            ->groupByRaw('COALESCE(union_types.slug, unions.union_type)')
            ->get();
        $groupedCounts = $countRows->pluck('aggregate', 'type_slug');
        $typeCounts = ['all' => (int) $countRows->sum('aggregate')];
        foreach (array_keys($typeLabels) as $typeKey) {
            $typeCounts[$typeKey] = (int) ($groupedCounts[$typeKey] ?? 0);
        }

        $viewData = compact('unions', 'search', 'type', 'categoryId', 'typeLabels', 'typeCounts');

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'html' => view('frontend.guilds.partials.results', $viewData)->render(),
                'current_page' => $unions->currentPage(),
                'last_page' => $unions->lastPage(),
                'total' => $unions->total(),
                'from' => $unions->firstItem(),
                'to' => $unions->lastItem(),
                'type_counts' => $typeCounts,
                'url' => $request->fullUrl(),
            ]);
        }

        return view('frontend.guilds.index', $viewData);
    }

    private function positiveInteger(mixed $value): ?int
    {
        $value = filter_var($value, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);

        return $value === false ? null : $value;
    }

    public function ajaxSearch(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $type = (string) $request->query('type', '');

        $unions = GuildUnion::query()
            ->active()
            ->with(['unionType', 'latestPublishedNews.featuredMedia'])
            ->when($search === '' && $type !== '', fn ($query) => $query->where(function ($query) use ($type) {
                $query->where('union_type', $type)
                    ->orWhereHas('unionType', fn ($typeQuery) => $typeQuery->where('slug', $type));
            }))
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('title', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%")
                ->orWhere('manager_name', 'like', "%{$search}%")
                ->orWhere('short_description', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")))
            ->orderBy('title')
            ->take(24)
            ->get();

        return response()->json([
            'items' => $unions->values()->map(function (GuildUnion $union, int $index) {
                $latestNews = $union->latestPublishedNews;
                $unionUrl = route('guilds.show', $union->slug);
                $unionImage = $union->primary_image;
                $description = $union->short_description ?: $union->manager_name ?: $union->union_type_label;

                return [
                    'title' => $union->display_title,
                    'description' => $description,
                    'url' => $unionUrl,
                    'image' => filled($unionImage) ? image_url($unionImage, 'assets/img/asnaf-gorgan-default.jpg') : asset('assets/img/asnaf-gorgan-default.jpg'),
                    'avatar_class' => 'avatar-'.(($index % 6) + 1),
                    'preview_url' => $latestNews ? route('posts.show', $latestNews->slug) : $unionUrl,
                    'preview_image' => $latestNews?->featured_image_url
                        ?: (filled($unionImage) ? image_url($unionImage, 'assets/img/asnaf-gorgan-default.jpg') : asset('assets/img/asnaf-gorgan-default.jpg')),
                    'preview_title' => $latestNews?->title ?: $union->display_title,
                    'preview_excerpt' => $latestNews?->summary ?: plain_text($description, 150),
                    'preview_label' => $latestNews ? 'آخرین خبر مرتبط' : 'معرفی اتحادیه',
                ];
            }),
        ]);
    }

    public function show(Request $request, string $slug): View|RedirectResponse|JsonResponse
    {
        if ($redirect = app(SlugRedirectService::class)->redirectIfLegacy(GuildUnion::class, $slug, 'guilds.show')) {
            return $redirect;
        }

        $union = GuildUnion::query()
            ->active()
            ->where('slug', $slug)
            ->firstOrFail();

        $union->load([
            'category',
            'unionType',
            'members' => fn ($q) => $q->where('is_active', true)->where('status', 'active')->orderBy('sort_order')->orderBy('id'),
            'commissions' => fn ($q) => $q->where('is_active', true)->with(['tasks' => fn ($t) => $t->where('is_active', true)->orderBy('sort_order')])->orderBy('sort_order'),
            'rules' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order'),
            'minutes' => fn ($q) => $q->where('is_active', true)->orderByDesc('meeting_date'),
            'educations' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order'),
            'prices' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order'),
            'posts' => fn ($q) => $q
                ->published()
                ->with(['category', 'featuredMedia'])
                ->latest('published_at'),
            'announcements' => fn ($q) => $q->published()->latest('published_at'),
            'galleries' => fn ($q) => $q->published()->forUnion()->with(['images'])->latest('published_at'),
            'videos' => fn ($q) => $q->published()->latest('published_at'),
        ]);

        $union->setRelation(
            'members',
            $union->members
                ->reject(fn ($member) => $member->isSeedPlaceholderProfile())
                ->values()
        );

        // Only editorial news directly associated with this union may appear here.
        // The union-specific page uses its own page parameter to avoid interfering
        // with the homepage and the global news archive.
        $unionNews = Post::query()
            ->where('union_id', $union->id)
            ->published()
            ->editorial()
            ->with(['category', 'featuredMedia'])
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(6, ['*'], 'news_page')
            ->withPath(route('guilds.show', $union->slug));

        if (($union->news_mode ?? 'auto') === 'disabled') {
            // Honour the admin's explicit disabled mode, even for AJAX requests.
            $unionNews->setCollection(collect());
        }

        if ($request->ajax() || $request->expectsJson()) {
            $newsVisible = $union->news_enabled
                && ($union->news_mode ?? 'auto') !== 'disabled'
                && $union->isSectionEnabled('show_news', true);

            return response()->json([
                'html' => view('frontend.guilds.partials.news-panel', [
                    'unionNews' => $newsVisible ? $unionNews : null,
                ])->render(),
                'current_page' => $unionNews->currentPage(),
                'last_page' => $newsVisible ? $unionNews->lastPage() : 1,
                'total' => $newsVisible ? $unionNews->total() : 0,
                'url' => $request->fullUrl(),
            ]);
        }

        $unionMessages = CongratulationMessage::where('is_active', true)
            ->where('status', 'published')
            ->where('show_on_union_page', true)
            ->where(function ($q) use ($union) {
                $q->whereNull('union_id')->orWhere('union_id', $union->id);
            })
            ->latest('published_at')
            ->take(6)
            ->get();

        return view('frontend.guilds.show', [
            'union' => $union,
            'unionNews' => $unionNews,
            'unionMessages' => $unionMessages,
        ]);
    }
}
