<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CongratulationMessage;
use App\Models\GuildUnion;
use App\Models\UnionType;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Services\SlugRedirectService;
use Illuminate\View\View;

class UnionController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $type = (string) $request->query('type', '');
        $categoryId = (string) $request->query('category_id', '');

        $baseQuery = GuildUnion::query()
            ->active()
            ->with(['category', 'unionType'])
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('title', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%")))
            ->when($type !== '', fn ($query) => $query->where(function ($query) use ($type) {
                $query->where('union_type', $type)
                    ->orWhereHas('unionType', fn ($typeQuery) => $typeQuery->where('slug', $type));
            }))
            ->when($categoryId !== '', fn ($query) => $query->where('category_id', $categoryId));

        $unions = (clone $baseQuery)->orderBy('title')->paginate(12)->withQueryString();

        $unionTypes = UnionType::query()->active()->orderBy('sort_order')->orderBy('title')->get();
        $typeTabs = $unionTypes->mapWithKeys(fn (UnionType $unionType) => [
            $unionType->slug => [
                'label' => $unionType->title,
                'icon' => $unionType->icon,
                'items' => (clone $baseQuery)->where('union_type_id', $unionType->id)->orderBy('title')->get(),
            ],
        ]);
        $categories = Category::query()->active()->where(fn ($query) => $query->whereNull('type')->orWhere('type', 'union')->orWhere('type', 'union_type'))->orderBy('sort_order')->orderBy('title')->get();

        return view('frontend.guilds.index', compact(
            'unions',
            'search',
            'unionTypes',
            'typeTabs',
            'categories',
            'type',
            'categoryId'
        ));
    }

    public function ajaxSearch(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $type = (string) $request->query('type', '');

        $unions = GuildUnion::query()
            ->active()
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
            'items' => $unions->values()->map(fn (GuildUnion $union, int $index) => [
                'title' => $union->display_title,
                'description' => $union->short_description ?: $union->manager_name ?: $union->union_type_label,
                'url' => route('guilds.show', $union->slug),
                'complaint_url' => $union->complaint_enabled
                    ? route('complaints.create', $union->id)
                    : null,
                'image' => ($union->logo || $union->cover_image) ? image_url($union->logo ?: $union->cover_image, '') : null,
                'avatar_class' => 'avatar-'.(($index % 6) + 1),
                'social_links' => $union->social_link_items,
            ]),
        ]);
    }

    public function show(string $slug): View|RedirectResponse
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
            'members' => fn ($q) => $q->where('is_active', true)->where('status', 'active')->orderBy('sort_order')->orderBy('full_name'),
            'commissions' => fn ($q) => $q->where('is_active', true)->with(['tasks' => fn ($t) => $t->where('is_active', true)->orderBy('sort_order')])->orderBy('sort_order'),
            'rules' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order'),
            'minutes' => fn ($q) => $q->where('is_active', true)->orderByDesc('meeting_date'),
            'educations' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order'),
            'prices' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order'),
            'posts' => fn ($q) => $q->published()->latest('published_at'),
            'selectedPosts' => fn ($q) => $q->published(),
            'announcements' => fn ($q) => $q->published()->latest('published_at'),
            'galleries' => fn ($q) => $q->published()->forUnion()->with(['images'])->latest('published_at'),
            'videos' => fn ($q) => $q->published()->latest('published_at'),
        ]);

        $unionMessages = CongratulationMessage::where('is_active', true)
            ->where('status', 'published')
            ->where('show_on_union_page', true)
            ->where(function ($q) use ($union) {
                $q->whereNull('union_id')->orWhere('union_id', $union->id);
            })
            ->latest('published_at')
            ->take(6)
            ->get();

        return view('frontend.guilds.show', ['union' => $union, 'unionMessages' => $unionMessages]);
    }
}
