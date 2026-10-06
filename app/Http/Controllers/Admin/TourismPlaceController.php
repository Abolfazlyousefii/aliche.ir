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
        $tourismType = trim((string) $request->query('tourism_type'));

        $places = TourismPlace::query()
            ->with(['category', 'creator'])
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('title', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%")
                ->orWhere('short_description', 'like', "%{$search}%")
                ->orWhere('address', 'like', "%{$search}%")))
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
            ->when(in_array($tourismType, TourismPlace::TYPES, true), fn ($query) => $query->where('tourism_type', $tourismType))
            ->orderBy('sort_order')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.tourism.index', [
            'places' => $places,
            'search' => $search,
            'status' => $status,
            'categoryId' => $categoryId,
            'tourismType' => $tourismType,
            'categories' => $this->categories(),
            'statusLabels' => TourismPlace::statusLabels(),
            'typeLabels' => TourismPlace::typeLabels(),
        ]);
    }

    public function create(): View
    {
        return view('admin.tourism.create', [
            ...$this->formData(),
            'place' => null,
            'currentCardMediaId' => null,
            'currentFeaturedMediaId' => null,
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
            ...$this->formData(),
            'place' => $tourism,
            'currentCardMediaId' => Media::query()->images()->where('path', $tourism->image)->value('id'),
            'currentFeaturedMediaId' => Media::query()->images()->where('path', $tourism->featured_image)->value('id'),
        ]);
    }

    public function update(Request $request, TourismPlace $tourism): RedirectResponse
    {
        $validated = $this->sanitizeRichTextFields($this->validatedData($request, $tourism), ['body', 'excerpt', 'short_description', 'description', 'content', 'footer_description', 'site_description']);
        $data = $this->placeData($validated, $tourism);

        if ($cardImage = $this->storeImage($request, 'image', 'tourism/cards')) {
            $data['image'] = $cardImage;
        }

        if ($featuredImage = $this->storeImage($request, 'featured_image', 'tourism/featured')) {
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
            'featured_image_media_id' => [
                'nullable',
                'integer',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (filled($value) && ! Media::query()->images()->whereKey($value)->exists()) {
                        $fail('تصویر شاخص انتخاب‌شده از کتابخانه معتبر نیست.');
                    }
                },
            ],
            'gallery_images' => ['nullable', 'array'],
            'gallery_images.*' => ['bail', 'file', new SafeImageUpload, 'max:'.config('media.max_upload_kilobytes', 5120)],
            'gallery_images_media_ids' => ['nullable', 'array'],
            'gallery_images_media_ids.*' => [
                'integer',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! Media::query()->images()->whereKey($value)->exists()) {
                        $fail('یکی از تصاویر انتخاب‌شده برای گالری معتبر نیست.');
                    }
                },
            ],
            'category_id' => [
                'nullable',
                Rule::exists('categories', 'id')->where(fn ($query) => $query
                    ->where('type', 'tourism')
                    ->where('is_active', true)),
            ],
            'badge' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'bail', 'file', new SafeImageUpload, 'max:'.config('media.max_upload_kilobytes', 5120)],
            'image_media_id' => [
                'nullable',
                'integer',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (filled($value) && ! Media::query()->images()->whereKey($value)->exists()) {
                        $fail('تصویر کارت انتخاب‌شده از کتابخانه معتبر نیست.');
                    }
                },
            ],
            'location' => ['nullable', 'string', 'max:255'],
            'tourism_type' => ['required', Rule::in(TourismPlace::TYPES)],
            'address' => ['nullable', 'string'],
            'map_url' => [
                'nullable',
                'string',
                'max:500',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (filled($value) && TourismPlace::normalizeMapUrl((string) $value) === null) {
                        $fail('لینک نقشه باید یک آدرس معتبر http یا https باشد.');
                    }
                },
            ],
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
            'featured_image_media_id' => 'تصویر شاخص از کتابخانه',
            'gallery_images' => 'گالری تصاویر',
            'gallery_images_media_ids' => 'تصاویر گالری از کتابخانه',
            'category_id' => 'دسته‌بندی',
            'badge' => 'برچسب',
            'image' => 'تصویر',
            'image_media_id' => 'تصویر کارت از کتابخانه',
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
            'map_url' => TourismPlace::normalizeMapUrl($validated['map_url'] ?? null),
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
        $selectedIds = collect($request->input('gallery_images_media_ids', []))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $selectedById = Media::query()
            ->whereIn('id', $selectedIds)
            ->images()
            ->get()
            ->keyBy('id');

        $selected = $selectedIds
            ->map(fn (int $id) => $selectedById->get($id))
            ->filter()
            ->values()
            ->map(fn (Media $media, int $index) => [
                'path' => $media->path,
                'caption' => $media->caption ?: $media->title,
                'sort_order' => $mediaStartOrder + (($index + 1) * 10),
            ]);

        return $uploaded->merge($selected)->unique('path')->values()->all();
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

        $kept = $gallery
            ->reject(fn ($image) => $image['delete'])
            ->map(fn ($image) => collect($image)->except('delete')->all())
            ->values();

        return $kept
            ->merge($this->storeGalleryImages($request, (int) $kept->max('sort_order')))
            ->filter(fn ($image) => filled($image['path'] ?? null))
            ->unique('path')
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

    private function formData(): array
    {
        return [
            'categories' => $this->categories(),
            'statusLabels' => TourismPlace::statusLabels(),
            'typeLabels' => TourismPlace::typeLabels(),
            'mediaItems' => Media::query()->images()->latest()->take(200)->get(),
        ];
    }

    private function categories()
    {
        return Category::query()->active()->where('type', 'tourism')->orderBy('sort_order')->orderBy('title')->get();
    }
}
