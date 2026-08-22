<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\SelectsMedia;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Media;
use App\Models\TourismPlace;
use App\Rules\SafeImageUpload;
use App\Services\ContentApprovalService;
use App\Services\MediaLibraryService;
use App\Services\SlugService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TourismPlaceController extends Controller
{
    use SelectsMedia;

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $status = (string) $request->query('status', '');
        $categoryId = $request->query('category_id');

        $places = TourismPlace::query()
            ->with(['category', 'creator'])
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('title', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%")
                ->orWhere('short_description', 'like', "%{$search}%")
                ->orWhere('address', 'like', "%{$search}%")))
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
            ->orderBy('sort_order')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.tourism.index', [
            'places' => $places,
            'search' => $search,
            'status' => $status,
            'categoryId' => $categoryId,
            'categories' => $this->categories(),
            'statusLabels' => TourismPlace::statusLabels(),
            'typeLabels' => TourismPlace::typeLabels(),
        ]);
    }

    public function create(): View
    {
        return view('admin.tourism.create', [
            'place' => null,
            'categories' => $this->categories(),
            'statusLabels' => TourismPlace::statusLabels(),
            'typeLabels' => TourismPlace::typeLabels(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->sanitizeRichTextFields($this->validatedData($request), ['body', 'excerpt', 'short_description', 'description', 'content', 'footer_description', 'site_description']);
        $status = $validated['status'];

        $place = TourismPlace::create([
            ...$this->placeData($validated),
            'featured_image' => $this->storeImage($request, 'featured_image', 'tourism/featured'),
            'image' => $this->storeImage($request, 'image', 'tourism/cards'),
            'gallery' => $this->storeGalleryImages($request),
            'created_by' => $request->user()->id,
            'approved_by' => in_array($status, ['approved', 'published'], true) ? $request->user()->id : null,
        ]);

        return redirect()->route('admin.tourism.show', $place)->with('success', 'مکان گردشگری با موفقیت ایجاد شد.');
    }

    public function show(TourismPlace $tourism): View
    {
        $tourism->load(['category', 'creator', 'approver']);

        return view('admin.tourism.show', ['place' => $tourism]);
    }

    public function edit(TourismPlace $tourism): View
    {
        return view('admin.tourism.edit', [
            'place' => $tourism,
            'categories' => $this->categories(),
            'statusLabels' => TourismPlace::statusLabels(),
            'typeLabels' => TourismPlace::typeLabels(),
        ]);
    }

    public function update(Request $request, TourismPlace $tourism): RedirectResponse
    {
        $validated = $this->sanitizeRichTextFields($this->validatedData($request, $tourism), ['body', 'excerpt', 'short_description', 'description', 'content', 'footer_description', 'site_description']);
        $data = $this->placeData($validated, $tourism);

        if ($cardImage = $this->storeImage($request, 'image', 'tourism/cards')) {
            if ($tourism->image) {
                Storage::disk('public')->delete($tourism->image);
            }

            $data['image'] = $cardImage;
        }

        if ($featuredImage = $this->storeImage($request, 'featured_image', 'tourism/featured')) {
            if ($tourism->featured_image) {
                Storage::disk('public')->delete($tourism->featured_image);
            }

            $data['featured_image'] = $featuredImage;
        }

        $data['gallery'] = $this->updatedGallery($request, $tourism);

        if (in_array($validated['status'], ['approved', 'published'], true) && ! $tourism->approved_by) {
            $data['approved_by'] = $request->user()->id;
        }

        $tourism->update($data);

        return redirect()->route('admin.tourism.show', $tourism)->with('success', 'مکان گردشگری با موفقیت ویرایش شد.');
    }

    public function destroy(TourismPlace $tourism): RedirectResponse
    {
        if ($tourism->featured_image) {
            Storage::disk('public')->delete($tourism->featured_image);
        }

        foreach ($tourism->gallery ?? [] as $image) {
            Storage::disk('public')->delete($image['path'] ?? '');
        }

        $tourism->delete();

        return redirect()->route('admin.tourism.index')->with('success', 'مکان گردشگری با موفقیت حذف شد.');
    }

    private function validatedData(Request $request, ?TourismPlace $place = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('tourism_places', 'slug')->ignore($place?->id)],
            'description' => ['nullable', 'string'],
            'short_description' => ['nullable', 'string', 'max:1000'],
            'featured_image' => ['nullable', 'bail', 'file', new SafeImageUpload, 'max:'.config('media.max_upload_kilobytes', 5120)],
            'gallery_images' => ['nullable', 'array'],
            'gallery_images.*' => ['bail', 'file', new SafeImageUpload, 'max:'.config('media.max_upload_kilobytes', 5120)],
            'category_id' => ['nullable', 'exists:categories,id'],
            'badge' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'bail', 'file', new SafeImageUpload, 'max:'.config('media.max_upload_kilobytes', 5120)],
            'location' => ['nullable', 'string', 'max:255'],
            'tourism_type' => ['required', Rule::in(TourismPlace::TYPES)],
            'address' => ['nullable', 'string'],
            'map_url' => ['nullable', 'url', 'max:500'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'phone' => ['nullable', 'string', 'max:255'],
            'working_hours' => ['nullable', 'string', 'max:255'],
            'visit_price' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(app(ContentApprovalService::class)->allowedStatusesFor($request->user(), ['tourism.approve', 'tourism.publish']))],
            'published_at' => ['nullable', 'date'],
            'rejected_reason' => ['nullable', 'required_if:status,rejected', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['required', Rule::in(['0', '1'])],
            'existing_gallery' => ['nullable', 'array'],
        ], [], [
            'title' => 'عنوان',
            'slug' => 'نامک',
            'description' => 'توضیحات',
            'short_description' => 'توضیح کوتاه',
            'featured_image' => 'تصویر شاخص',
            'gallery_images' => 'گالری تصاویر',
            'category_id' => 'دسته‌بندی',
            'badge' => 'برچسب',
            'image' => 'تصویر',
            'location' => 'موقعیت',
            'tourism_type' => 'نوع گردشگری',
            'address' => 'آدرس',
            'map_url' => 'لینک نقشه',
            'latitude' => 'عرض جغرافیایی',
            'longitude' => 'طول جغرافیایی',
            'phone' => 'تلفن',
            'working_hours' => 'ساعت بازدید',
            'visit_price' => 'هزینه بازدید',
            'status' => 'وضعیت',
            'published_at' => 'تاریخ انتشار',
            'rejected_reason' => 'دلیل رد',
            'sort_order' => 'ترتیب نمایش',
            'is_active' => 'فعال',
        ]);
    }

    private function placeData(array $validated, ?TourismPlace $place = null): array
    {
        $validated = $this->sanitizeRichTextFields($validated, ['body', 'excerpt', 'short_description', 'description', 'content', 'footer_description', 'site_description']);

        $status = $validated['status'];
        $publishedAt = $validated['published_at'] ?? null;

        if ($status === 'published' && ! $publishedAt) {
            $publishedAt = $place?->published_at ?: now();
        }

        return [
            'title' => $validated['title'],
            'slug' => $this->uniqueSlug($validated['slug'] ?: $validated['title'], $place),
            'description' => $validated['description'] ?? null,
            'short_description' => $validated['short_description'] ?? null,
            'category_id' => $validated['category_id'] ?? null,
            'badge' => $validated['badge'] ?? null,

            'location' => $validated['location'] ?? null,
            'tourism_type' => $validated['tourism_type'] ?? 'nature',
            'type' => $validated['tourism_type'] ?? 'nature',
            'address' => $validated['address'] ?? null,
            'map_url' => $validated['map_url'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'working_hours' => $validated['working_hours'] ?? null,
            'visit_price' => $validated['visit_price'] ?? null,
            'status' => $status,
            'published_at' => $publishedAt,
            'rejected_reason' => $validated['rejected_reason'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => (bool) $validated['is_active'],
        ];
    }

    private function storeGalleryImages(Request $request, int $startOrder = 0): array
    {
        $uploaded = $request->hasFile('gallery_images')
            ? collect($request->file('gallery_images'))->map(fn ($file, $index) => [
                'path' => app(MediaLibraryService::class)->storeImage($file, 'tourism/gallery', 'public', $request->user()?->id)->path,
                'caption' => null,
                'sort_order' => $startOrder + (($index + 1) * 10),
            ])
            : collect();

        $mediaStartOrder = $startOrder + ($uploaded->count() * 10);
        $selected = Media::query()
            ->whereIn('id', collect($request->input('gallery_images_media_ids', []))->filter()->unique()->values())
            ->images()
            ->get()
            ->values()
            ->map(fn (Media $media, int $index) => [
                'path' => $media->path,
                'caption' => $media->caption ?: $media->title,
                'sort_order' => $mediaStartOrder + (($index + 1) * 10),
            ]);

        return $uploaded->merge($selected)->all();
    }

    private function updatedGallery(Request $request, TourismPlace $place): array
    {
        $gallery = collect($place->gallery ?? []);
        $existing = collect($request->input('existing_gallery', []));

        $gallery = $gallery->map(function ($image, $index) use ($existing) {
            $payload = $existing->get((string) $index, []);

            return [
                'path' => $image['path'] ?? '',
                'caption' => $payload['caption'] ?? ($image['caption'] ?? null),
                'sort_order' => (int) ($payload['sort_order'] ?? ($image['sort_order'] ?? (($index + 1) * 10))),
                'delete' => ($payload['delete'] ?? null) === '1',
            ];
        });

        $gallery->filter(fn ($image) => $image['delete'])->each(fn ($image) => Storage::disk('public')->delete($image['path']));

        $kept = $gallery
            ->reject(fn ($image) => $image['delete'])
            ->map(fn ($image) => collect($image)->except('delete')->all())
            ->values();

        return $kept
            ->merge($this->storeGalleryImages($request, (int) $kept->max('sort_order')))
            ->sortBy('sort_order')
            ->values()
            ->all();
    }

    private function storeImage(Request $request, string $field, string $directory): ?string
    {
        return $this->uploadedOrSelectedImage($request, $field, $directory);
    }

    private function uniqueSlug(string $value, ?TourismPlace $place = null): string
    {
        return app(SlugService::class)->unique(TourismPlace::class, $value, $place?->id, 'slug', strtolower(class_basename(TourismPlace::class)));

        $baseSlug = Str::slug($value) ?: Str::random(8);
        $slug = $baseSlug;
        $counter = 2;

        while (TourismPlace::query()
            ->where('slug', $slug)
            ->when($place, fn ($query) => $query->whereKeyNot($place->id))
            ->exists()) {
            $slug = $baseSlug.'-'.$counter++;
        }

        return $slug;
    }

    private function categories()
    {
        return Category::query()->active()->where('type', 'tourism')->orderBy('sort_order')->orderBy('title')->get();
    }
}
