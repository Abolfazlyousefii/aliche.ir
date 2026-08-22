<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use App\Support\PublicFileUrl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Services\SlugRedirectService;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class GalleryController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $search = mb_substr(trim((string) $request->query('search')), 0, 100);

        $galleries = Gallery::query()
            ->published()
            ->with([
                'union:id,name,title,slug',
                'images:id,gallery_id,image,sort_order',
            ])
            ->withCount('images')
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhereHas('union', fn ($query) => $query
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%"))))
            ->orderBy('sort_order')
            ->latest('published_at')
            ->paginate(12)
            ->appends(array_filter(['search' => $search]));

        $covers = $galleries->getCollection()->mapWithKeys(fn (Gallery $gallery): array => [
            $gallery->id => $this->coverUrl($gallery),
        ]);
        $typeCounts = [
            'all' => Gallery::query()->published()->count(),
            'image' => Gallery::query()->published()->whereHas('images')->count(),
            'video' => 0,
            'mixed' => 0,
        ];
        $viewData = [
            'galleries' => $galleries,
            'search' => $search,
            'type' => '',
            'typeCounts' => $typeCounts,
            'covers' => $covers,
            'showTypeTabs' => false,
        ];

        if ($request->ajax() || $request->wantsJson()) {
            $responseUrl = route('galleries.index', array_filter([
                'search' => $search,
                'page' => $galleries->currentPage() > 1 ? $galleries->currentPage() : null,
            ]));

            return response()->json([
                'html' => view('frontend.galleries.partials.results', $viewData)->render(),
                'current_page' => $galleries->currentPage(),
                'last_page' => $galleries->lastPage(),
                'total' => $galleries->total(),
                'from' => $galleries->firstItem(),
                'to' => $galleries->lastItem(),
                'type_counts' => $typeCounts,
                'url' => $responseUrl,
            ]);
        }

        return view('frontend.galleries.index', $viewData);
    }

    public function show(string $slug): View|RedirectResponse
    {
        if ($redirect = app(SlugRedirectService::class)->redirectIfLegacy(Gallery::class, $slug, 'galleries.show')) {
            return $redirect;
        }

        $gallery = Gallery::query()
            ->published()
            ->with(['union', 'images'])
            ->where('slug', $slug)
            ->firstOrFail();

        $relatedGalleries = Gallery::query()
            ->published()
            ->whereKeyNot($gallery->id)
            ->withCount('images')
            ->orderBy('sort_order')
            ->latest('published_at')
            ->take(6)
            ->get();

        return view('frontend.galleries.show', compact('gallery', 'relatedGalleries'));
    }

    private function coverUrl(Gallery $gallery): ?string
    {
        foreach (collect([$gallery->cover_image])->concat($gallery->images->pluck('image')) as $path) {
            if ($url = $this->existingMediaUrl($path)) {
                return $url;
            }
        }

        return null;
    }

    private function existingMediaUrl(?string $path): ?string
    {
        $path = trim((string) $path);
        if ($path === '' || str_contains($path, '..')) {
            return null;
        }

        $normalized = PublicFileUrl::normalizeStoragePath($path);
        if ($normalized === 'assets/img/asnaf-gorgan-default.jpg') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            $localPath = PublicFileUrl::sameApplicationStoragePath($path);

            return $localPath === null || $this->storageFileExists($localPath) ? PublicFileUrl::make($path, '') : null;
        }

        $publicFile = public_path(str_replace('/', DIRECTORY_SEPARATOR, ltrim($normalized, '/')));
        if (is_file($publicFile)) {
            return asset($normalized);
        }

        foreach (['storage', 'media-files', 'uploaded-media'] as $publicDirectory) {
            $publicMediaFile = public_path($publicDirectory.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $normalized));
            if (is_file($publicMediaFile)) {
                return asset($publicDirectory.'/'.$normalized);
            }
        }

        return $this->storageFileExists($normalized) ? PublicFileUrl::make($normalized, '') : null;
    }

    private function storageFileExists(string $path): bool
    {
        $normalized = PublicFileUrl::normalizeStoragePath($path);
        if (Storage::disk('public')->exists($normalized)) {
            return true;
        }

        $relative = str_replace('/', DIRECTORY_SEPARATOR, $normalized);
        $roots = array_unique(array_filter([
            config('filesystems.disks.public.root'),
            storage_path('app/public'),
            public_path('storage'),
            public_path('media'),
            public_path('media-files'),
            public_path('uploaded-media'),
        ]));

        foreach ($roots as $root) {
            if (is_file(rtrim($root, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$relative)) {
                return true;
            }
        }

        return false;
    }
}
