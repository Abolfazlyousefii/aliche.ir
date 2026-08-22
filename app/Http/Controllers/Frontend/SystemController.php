<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\System;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Services\SlugRedirectService;
use Illuminate\View\View;

class SystemController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $categoryInput = mb_substr(trim((string) $request->query('category')), 0, 100);
        $search = mb_substr(trim((string) $request->query('search')), 0, 100);
        $publishedCategoryIds = System::query()
            ->published()
            ->whereNotNull('category_id')
            ->select('category_id');
        $categories = Category::query()
            ->active()
            ->where('type', 'system')
            ->whereIn('id', $publishedCategoryIds)
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();
        $selectedCategory = $categories->first(fn (Category $category): bool =>
            $category->slug === $categoryInput
            || (ctype_digit($categoryInput) && $category->id === (int) $categoryInput)
        );
        $activeCategory = $selectedCategory?->slug ?? '';

        $systems = System::query()
            ->published()
            ->with('category')
            ->when($selectedCategory, fn ($query) => $query->where('category_id', $selectedCategory->id))
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('title', 'like', "%{$search}%")
                ->orWhere('short_description', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhereHas('category', fn ($query) => $query
                    ->where('type', 'system')
                    ->where('is_active', true)
                    ->where('title', 'like', "%{$search}%"))))
            ->orderBy('sort_order')
            ->orderBy('title')
            ->paginate(12)
            ->withQueryString();

        $categoryTotals = System::query()
            ->published()
            ->whereIn('category_id', $categories->pluck('id'))
            ->selectRaw('category_id, COUNT(*) AS total')
            ->groupBy('category_id')
            ->pluck('total', 'category_id');
        $categoryCounts = ['all' => System::query()->published()->count()];
        foreach ($categories as $category) {
            $categoryCounts[$category->slug] = (int) ($categoryTotals[$category->id] ?? 0);
        }

        $entryLinks = $systems->getCollection()->mapWithKeys(fn (System $system): array => [
            $system->id => $this->entryLink($system, $request),
        ]);
        $viewData = [
            'systems' => $systems,
            'categories' => $categories,
            'activeCategory' => $activeCategory,
            'search' => $search,
            'categoryCounts' => $categoryCounts,
            'entryLinks' => $entryLinks,
            'activeTotal' => $categoryCounts['all'],
        ];

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'html' => view('frontend.systems.partials.results', $viewData)->render(),
                'current_page' => $systems->currentPage(),
                'last_page' => $systems->lastPage(),
                'total' => $systems->total(),
                'from' => $systems->firstItem(),
                'to' => $systems->lastItem(),
                'category_counts' => $categoryCounts,
                'url' => $request->fullUrl(),
            ]);
        }

        return view('frontend.systems.index', $viewData);
    }

    public function show(string $slug): View|RedirectResponse
    {
        if ($redirect = app(SlugRedirectService::class)->redirectIfLegacy(System::class, $slug, 'systems.show')) {
            return $redirect;
        }

        $system = System::query()
            ->published()
            ->with('category')
            ->where('slug', $slug)
            ->firstOrFail();

        $relatedSystems = System::query()
            ->published()
            ->with('category')
            ->whereKeyNot($system->id)
            ->when($system->category_id, fn ($query) => $query->where('category_id', $system->category_id))
            ->orderBy('sort_order')
            ->take(4)
            ->get();

        if ($relatedSystems->isEmpty()) {
            $relatedSystems = System::query()
                ->published()
                ->with('category')
                ->whereKeyNot($system->id)
                ->orderBy('sort_order')
                ->take(4)
                ->get();
        }

        return view('frontend.systems.show', compact('system', 'relatedSystems'));
    }

    private function entryLink(System $system, Request $request): ?array
    {
        $link = trim((string) $system->link);
        if ($link === '' || $link === '#') {
            return null;
        }

        if (str_starts_with($link, '/')) {
            $url = url($link);
            $external = false;
        } else {
            if (! filter_var($link, FILTER_VALIDATE_URL)) {
                return null;
            }

            $scheme = strtolower((string) parse_url($link, PHP_URL_SCHEME));
            $host = strtolower((string) parse_url($link, PHP_URL_HOST));
            if (! in_array($scheme, ['http', 'https'], true) || $host === '' || in_array($host, ['example.com', 'www.example.com'], true)) {
                return null;
            }

            $url = $link;
            $external = ! hash_equals(strtolower($request->getHost()), $host);
        }

        if (rtrim($url, '/') === rtrim(route('systems.show', $system), '/')) {
            return null;
        }

        return ['url' => $url, 'external' => $external];
    }
}
