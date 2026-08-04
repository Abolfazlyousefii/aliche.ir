<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ElectronicService;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Services\SlugRedirectService;
use Illuminate\View\View;

class ElectronicServiceController extends Controller
{
    public function index(Request $request): View
    {
        $category = trim((string) $request->query('category'));
        $search = trim((string) $request->query('search'));

        $services = ElectronicService::query()
            ->published()
            ->with('category')
            ->when($category !== '', fn ($query) => $query->whereHas('category', fn ($query) => $query
                ->where('slug', $category)
                ->when(ctype_digit($category), fn ($query) => $query->orWhere('id', (int) $category))))
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('title', 'like', "%{$search}%")
                ->orWhere('short_description', 'like', "%{$search}%")
                ->orWhere('body', 'like', "%{$search}%")))
            ->orderBy('sort_order')
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('frontend.electronic_services.index', [
            'services' => $services,
            'categories' => $this->categories(),
            'activeCategory' => $category,
            'search' => $search,
        ]);
    }

    public function show(string $slug): View|RedirectResponse
    {
        if ($redirect = app(SlugRedirectService::class)->redirectIfLegacy(ElectronicService::class, $slug, 'electronic-services.show')) {
            return $redirect;
        }

        $service = ElectronicService::query()
            ->published()
            ->with('category')
            ->where('slug', $slug)
            ->firstOrFail();

        $relatedServices = ElectronicService::query()
            ->published()
            ->with('category')
            ->whereKeyNot($service->id)
            ->when($service->category_id, fn ($query) => $query->where('category_id', $service->category_id))
            ->orderBy('sort_order')
            ->take(4)
            ->get();

        return view('frontend.electronic_services.show', compact('service', 'relatedServices'));
    }

    private function categories()
    {
        return Category::query()
            ->active()
            ->where('type', 'service')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();
    }
}
