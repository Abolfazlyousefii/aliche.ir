<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\TourismPlace;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Services\SlugRedirectService;
use Illuminate\View\View;

class TourismController extends Controller
{
    public function index(Request $request, SettingService $settings): View|JsonResponse
    {
        $types = $this->directoryTypes();
        $requestedType = trim((string) $request->query('type'));
        $activeType = array_key_exists($requestedType, $types) ? $requestedType : null;

        $baseQuery = TourismPlace::query()->published();
        $databaseCounts = (clone $baseQuery)
            ->selectRaw('tourism_type, COUNT(*) as aggregate')
            ->groupBy('tourism_type')
            ->pluck('aggregate', 'tourism_type');

        $typeCounts = collect($types)->mapWithKeys(fn ($label, $type) => [
            $type => (int) ($databaseCounts[$type] ?? 0),
        ])->all();
        $typeCounts = ['all' => (int) $databaseCounts->sum()] + $typeCounts;

        $places = (clone $baseQuery)
            ->when($activeType, fn ($query) => $query->where('tourism_type', $activeType))
            ->orderBy('sort_order')
            ->latest('published_at')
            ->get();

        $allPlaces = $activeType
            ? (clone $baseQuery)->orderBy('sort_order')->latest('published_at')->get()
            : $places;
        $galleryItems = $this->galleryItems($allPlaces);
        $introImageUrl = $this->introImageUrl($settings->group('tourism'), $allPlaces);

        $viewData = compact(
            'places', 'types', 'typeCounts', 'activeType', 'galleryItems', 'introImageUrl'
        );

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'html' => view('frontend.tourism.partials.results', $viewData)->render(),
                'total' => $places->count(),
                'active_type' => $activeType,
                'type_counts' => $typeCounts,
                'url' => $activeType ? route('tourism.index', ['type' => $activeType]) : route('tourism.index'),
            ]);
        }

        return view('frontend.tourism.index', $viewData + [
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

    private function directoryTypes(): array
    {
        return [
            'nature' => 'طبیعت‌گردی',
            'historic' => 'تاریخی و فرهنگی',
            'shopping' => 'بازار و خرید',
        ];
    }

    private function galleryItems($places): array
    {
        return $places->flatMap(fn (TourismPlace $place) => $place->gallery_items)
            ->concat($places->map(fn (TourismPlace $place) => $place->directory_image_url ? [
                'url' => $place->directory_image_url,
                'caption' => $place->title,
            ] : null)->filter())
            ->unique('url')
            ->take(6)
            ->values()
            ->all();
    }

    private function introImageUrl(array $settings, $places): string
    {
        $settingImage = $settings['tourism.intro_image'] ?? null;
        $settingUrl = $settingImage ? image_url($settingImage, '') : '';

        return $settingUrl
            ?: (string) $places->first(fn (TourismPlace $place) => $place->directory_image_url)?->directory_image_url
            ?: asset('assets/img/tourism-placeholder.svg');
    }
}
