<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\TourismPlace;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Services\SlugRedirectService;
use Illuminate\View\View;

class TourismController extends Controller
{
    public function index(Request $request, SettingService $settings): View
    {
        $category = trim((string) $request->query('category'));
        $search = trim((string) $request->query('search'));

        $placesQuery = TourismPlace::query()
            ->published()
            ->with('category')
            ->when($category !== '', fn ($query) => $query->whereHas('category', fn ($query) => $query
                ->where('slug', $category)
                ->when(ctype_digit($category), fn ($query) => $query->orWhere('id', (int) $category))))
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('title', 'like', "%{$search}%")
                ->orWhere('short_description', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhere('address', 'like', "%{$search}%")))
            ->orderBy('sort_order')
            ->latest('published_at');

        $places = $placesQuery->get();
        $tourismPanels = $this->categoryPanels($places);
        $tourismNature = $tourismPanels->first()['items'] ?? collect();
        $tourismHistoric = $tourismPanels->skip(1)->first()['items'] ?? collect();
        $tourismShop = $tourismPanels->skip(2)->first()['items'] ?? collect();
        $galleryPlaces = $places->take(6);

        return view('frontend.tourism.index', [
            'places' => $places,
            'tourismNature' => $tourismNature,
            'tourismPanels' => $tourismPanels,
            'tourismHistoric' => $tourismHistoric,
            'tourismShop' => $tourismShop,
            'galleryPlaces' => $galleryPlaces,
            'categories' => $this->categories(),
            'activeCategory' => $category,
            'search' => $search,
            'tourismSettings' => $settings->group('tourism'),
        ]);
    }

    public function show(string $slug): View|RedirectResponse
    {
        if ($redirect = app(SlugRedirectService::class)->redirectIfLegacy(TourismPlace::class, $slug, 'tourism.show')) {
            return $redirect;
        }

        $place = TourismPlace::query()
            ->published()
            ->with('category')
            ->where('slug', $slug)
            ->firstOrFail();

        $relatedPlaces = TourismPlace::query()
            ->published()
            ->with('category')
            ->whereKeyNot($place->id)
            ->when($place->category_id, fn ($query) => $query->where('category_id', $place->category_id))
            ->orderBy('sort_order')
            ->latest('published_at')
            ->take(4)
            ->get();

        if ($relatedPlaces->isEmpty()) {
            $relatedPlaces = TourismPlace::query()
                ->published()
                ->with('category')
                ->whereKeyNot($place->id)
                ->orderBy('sort_order')
                ->latest('published_at')
                ->take(4)
                ->get();
        }

        return view('frontend.tourism.show', [
            'place' => $place,
            'relatedPlaces' => $relatedPlaces,
        ]);
    }

    private function categories()
    {
        return Category::query()
            ->active()
            ->where('type', 'tourism')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();
    }

    private function categoryPanels($places)
    {
        $categories = $this->categories();
        $panels = $categories->mapWithKeys(fn (Category $category) => [
            'tour-category-'.$category->id => [
                'label' => $category->title,
                'items' => $places->where('category_id', $category->id)->values(),
            ],
        ])->filter(fn (array $panel) => $panel['items']->isNotEmpty());

        if ($panels->isNotEmpty()) {
            return $panels;
        }

        return collect([
            'tour-all' => [
                'label' => 'همه جاذبه‌ها',
                'items' => $places,
            ],
        ]);
    }
}
