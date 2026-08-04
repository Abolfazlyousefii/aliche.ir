<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Category;
use App\Models\GuildUnion;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Services\SlugRedirectService;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $categoryId = $request->query('category_id');
        $unionId = $request->query('union_id');

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

        return view('frontend.announcements.index', [
            'announcements' => $announcements,
            'categories' => Category::query()->active()->where('type', 'news')->orderBy('sort_order')->orderBy('title')->get(),
            'unions' => GuildUnion::query()->where('is_active', true)->orderBy('title')->orderBy('name')->get(),
            'search' => $search,
            'categoryId' => $categoryId,
            'unionId' => $unionId,
        ]);
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
