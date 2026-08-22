<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\AnnouncementCategory;
use App\Models\GuildUnion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Services\SlugRedirectService;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $search = trim((string) $request->query('search'));
        $categoryId = $this->positiveInteger($request->query('category_id'));
        $unionId = $this->positiveInteger($request->query('union_id'));

        $publishedAnnouncements = fn ($query) => $query->published();

        $announcements = Announcement::query()
            ->published()
            ->with(['category', 'union'])
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('title', 'like', "%{$search}%")
                ->orWhere('excerpt', 'like', "%{$search}%")
                ->orWhere('body', 'like', "%{$search}%")))
            ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
            ->when($unionId, fn ($query) => $query->where('union_id', $unionId))
            ->orderByDesc('is_important')
            ->orderBy('sort_order')
            ->latest('published_at')
            ->paginate(12)
            ->withQueryString();

        $viewData = [
            'announcements' => $announcements,
            'search' => $search,
            'categoryId' => $categoryId,
            'unionId' => $unionId,
            'hasActiveFilters' => $search !== '' || $categoryId || $unionId,
        ];

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'html' => view('frontend.announcements.partials.results', $viewData)->render(),
                'current_page' => $announcements->currentPage(),
                'last_page' => $announcements->lastPage(),
                'total' => $announcements->total(),
                'from' => $announcements->firstItem(),
                'to' => $announcements->lastItem(),
                'url' => $request->fullUrl(),
            ]);
        }

        return view('frontend.announcements.index', array_merge($viewData, [
            'categories' => AnnouncementCategory::query()
                ->where('is_active', true)
                ->whereHas('announcements', $publishedAnnouncements)
                ->orderBy('sort_order')
                ->orderBy('title')
                ->get(),
            'unions' => GuildUnion::query()
                ->where('is_active', true)
                ->where(fn ($query) => $query
                    ->whereHas('announcements', $publishedAnnouncements)
                    ->when($unionId, fn ($query) => $query->orWhere(
                        $query->getModel()->getQualifiedKeyName(),
                        $unionId
                    )))
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

    public function show(string $slug): View|RedirectResponse
    {
        if ($redirect = app(SlugRedirectService::class)->redirectIfLegacy(Announcement::class, $slug, 'announcements.show')) {
            return $redirect;
        }

        $announcement = Announcement::query()
            ->published()
            ->where('slug', $slug)
            ->with(['category', 'union', 'author'])
            ->firstOrFail();

        $relatedAnnouncements = Announcement::query()
            ->published()
            ->whereKeyNot($announcement->id)
            ->when($announcement->category_id, fn ($query) => $query->where('category_id', $announcement->category_id))
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('frontend.announcements.show', compact('announcement', 'relatedAnnouncements'));
    }
}
