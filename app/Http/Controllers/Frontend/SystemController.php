<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\System;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Services\SlugRedirectService;
use Illuminate\View\View;

class SystemController extends Controller
{
    public function index(Request $request): View
    {
        $category = trim((string) $request->query('category'));
        $search = trim((string) $request->query('search'));

        $systems = System::query()
            ->published()
            ->with('category')
            ->when($category !== '', fn ($query) => $query->whereHas('category', fn ($query) => $query
                ->where('slug', $category)
                ->when(ctype_digit($category), fn ($query) => $query->orWhere('id', (int) $category))))
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('title', 'like', "%{$search}%")
                ->orWhere('short_description', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")))
            ->orderBy('sort_order')
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('frontend.systems.index', [
            'systems' => $systems,
            'categories' => $this->categories(),
            'activeCategory' => $category,
            'search' => $search,
        ]);
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

    private function categories()
    {
        return Category::query()
            ->active()
            ->where('type', 'system')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();
    }
}
