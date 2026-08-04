<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Concerns\SelectsMedia;
use App\Models\Category;
use App\Models\Gallery;
use App\Models\GalleryImage;
use App\Models\GuildUnion;
use App\Models\Media;
use App\Services\MediaLibraryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GalleryController extends Controller
{
    use SelectsMedia;
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $status = (string) $request->query('status', '');
        $unionId = $request->query('union_id');

        $galleries = Gallery::query()
            ->with(['union', 'creator'])
            ->withCount('images')
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('title', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")))
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($unionId, fn ($query) => $query->where('union_id', $unionId))
            ->orderBy('sort_order')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.galleries.index', [
            'galleries' => $galleries,
            'search' => $search,
            'status' => $status,
            'unionId' => $unionId,
            'unions' => $this->unions(),
            'categories' => Category::query()->active()->where('type', 'gallery')->orderBy('sort_order')->orderBy('title')->get(),
            'statusLabels' => Gallery::statusLabels(),
            'displayLocationLabels' => Gallery::displayLocationLabels(),
            'mediaItems' => Media::query()->images()->latest()->take(200)->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.galleries.create', [
            'gallery' => null,
            'unions' => $this->unions(),
            'categories' => Category::query()->active()->where('type', 'gallery')->orderBy('sort_order')->orderBy('title')->get(),
            'statusLabels' => Gallery::statusLabels(),
            'displayLocationLabels' => Gallery::displayLocationLabels(),
            'mediaItems' => Media::query()->images()->latest()->take(200)->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->sanitizeRichTextFields($this->validatedData($request), ['body', 'excerpt', 'short_description', 'description', 'content', 'footer_description', 'site_description']);
        $status = $validated['status'];

        $gallery = Gallery::create([
            ...$this->galleryData($validated),
            'cover_image' => $this->storeImage($request, 'cover_image', 'galleries/covers'),
            'created_by' => $request->user()->id,
            'approved_by' => in_array($status, ['approved', 'published'], true) ? $request->user()->id : null,
        ]);

        $this->storeGalleryImages($request, $gallery);
        $this->attachMediaImages($request, $gallery);
        $this->ensureCoverImage($gallery);

        return redirect()->route('admin.galleries.show', $gallery)->with('success', 'گالری تصاویر با موفقیت ایجاد شد.');
    }

    public function show(Gallery $gallery): View
    {
        $gallery->load(['union', 'creator', 'approver', 'images']);

        return view('admin.galleries.show', compact('gallery'));
    }

    public function edit(Gallery $gallery): View
    {
        $gallery->load('images');

        return view('admin.galleries.edit', [
            'gallery' => $gallery,
            'unions' => $this->unions(),
            'categories' => Category::query()->active()->where('type', 'gallery')->orderBy('sort_order')->orderBy('title')->get(),
            'statusLabels' => Gallery::statusLabels(),
            'displayLocationLabels' => Gallery::displayLocationLabels(),
            'mediaItems' => Media::query()->images()->latest()->take(200)->get(),
        ]);
    }

    public function update(Request $request, Gallery $gallery): RedirectResponse
    {
        $validated = $this->sanitizeRichTextFields($this->validatedData($request, $gallery), ['body', 'excerpt', 'short_description', 'description', 'content', 'footer_description', 'site_description']);
        $data = $this->galleryData($validated, $gallery);

        if ($coverImage = $this->storeImage($request, 'cover_image', 'galleries/covers')) {
            if ($gallery->cover_image && ! $gallery->images()->where('image', $gallery->cover_image)->exists()) {
                Storage::disk('public')->delete($gallery->cover_image);
            }

            $data['cover_image'] = $coverImage;
        }

        if (in_array($validated['status'], ['approved', 'published'], true) && ! $gallery->approved_by) {
            $data['approved_by'] = $request->user()->id;
        }

        $gallery->update($data);
        $this->syncExistingImages($request, $gallery);
        $this->storeGalleryImages($request, $gallery);
        $this->attachMediaImages($request, $gallery);
        $this->ensureCoverImage($gallery->refresh());

        return redirect()->route('admin.galleries.show', $gallery)->with('success', 'گالری تصاویر با موفقیت ویرایش شد.');
    }

    public function destroy(Gallery $gallery): RedirectResponse
    {
        $gallery->load('images');

        if ($gallery->cover_image) {
            Storage::disk('public')->delete($gallery->cover_image);
        }

        foreach ($gallery->images as $image) {
            Storage::disk('public')->delete($image->image);
        }

        $gallery->delete();

        return redirect()->route('admin.galleries.index')->with('success', 'گالری تصاویر با موفقیت حذف شد.');
    }

    public function sort(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'items' => ['required', 'array'],
            'items.*' => ['integer', 'exists:galleries,id'],
        ]);

        foreach ($validated['items'] as $index => $id) {
            Gallery::query()->whereKey($id)->update(['sort_order' => ($index + 1) * 10]);
        }

        return back()->with('success', 'ترتیب گالری‌ها ذخیره شد.');
    }

    private function validatedData(Request $request, ?Gallery $gallery = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('galleries', 'slug')->ignore($gallery?->id)],
            'description' => ['nullable', 'string'],
            'cover_image' => ['nullable', 'image', 'max:4096'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'union_id' => ['nullable', 'exists:unions,id'],
            'display_location' => ['required', Rule::in(['home', 'union', 'both'])],
            'status' => ['required', Rule::in(app(\App\Services\ContentApprovalService::class)->allowedStatusesFor($request->user(), ['galleries.approve', 'galleries.publish']))],
            'published_at' => ['nullable', 'date'],
            'rejected_reason' => ['nullable', 'required_if:status,rejected', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['required', Rule::in(['0', '1'])],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'max:4096'],
            'image_captions' => ['nullable', 'array'],
            'existing_images' => ['nullable', 'array'],
            'gallery_media_ids' => ['nullable', 'array'],
            'gallery_media_ids.*' => ['integer', 'exists:media,id'],
        ], [], [
            'title' => 'عنوان',
            'slug' => 'نامک',
            'description' => 'توضیحات',
            'cover_image' => 'تصویر کاور',
            'category_id' => 'دسته‌بندی',
            'union_id' => 'اتحادیه',
            'display_location' => 'محل نمایش',
            'status' => 'وضعیت',
            'published_at' => 'تاریخ انتشار',
            'rejected_reason' => 'دلیل رد',
            'sort_order' => 'ترتیب نمایش',
            'is_active' => 'فعال',
            'images' => 'تصاویر گالری',
        ]);
    }

    private function galleryData(array $validated, ?Gallery $gallery = null): array
    {
        $validated = $this->sanitizeRichTextFields($validated, ['body', 'excerpt', 'short_description', 'description', 'content', 'footer_description', 'site_description']);

        $status = $validated['status'];
        $publishedAt = $validated['published_at'] ?? null;

        if ($status === 'published' && ! $publishedAt) {
            $publishedAt = $gallery?->published_at ?: now();
        }

        return [
            'title' => $validated['title'],
            'slug' => $this->uniqueSlug($validated['slug'] ?: $validated['title'], $gallery),
            'description' => $validated['description'] ?? null,
            'category_id' => $validated['category_id'] ?? null,
            'union_id' => $validated['union_id'] ?? null,
            'display_location' => $validated['display_location'] ?? 'both',
            'status' => $status,
            'published_at' => $publishedAt,
            'rejected_reason' => $validated['rejected_reason'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => (bool) $validated['is_active'],
        ];
    }

    private function storeGalleryImages(Request $request, Gallery $gallery): void
    {
        if (! $request->hasFile('images')) {
            return;
        }

        $startOrder = (int) $gallery->images()->max('sort_order');
        $captions = $request->input('image_captions', []);

        foreach ($request->file('images') as $index => $file) {
            GalleryImage::create([
                'gallery_id' => $gallery->id,
                'image' => app(MediaLibraryService::class)->storeImage($file, 'galleries/images', 'public', $request->user()?->id)->path,
                'caption' => $captions[$index] ?? null,
                'sort_order' => $startOrder + (($index + 1) * 10),
            ]);
        }
    }

    private function attachMediaImages(Request $request, Gallery $gallery): void
    {
        $mediaIds = collect($request->input('gallery_media_ids', []))
            ->filter()
            ->unique()
            ->values();

        if ($mediaIds->isEmpty()) {
            return;
        }

        $startOrder = (int) $gallery->images()->max('sort_order');
        $existingPaths = $gallery->images()->pluck('image')->all();

        Media::query()
            ->whereIn('id', $mediaIds)
            ->images()
            ->get()
            ->each(function (Media $media, int $index) use ($gallery, &$existingPaths, $startOrder) {
                if (in_array($media->path, $existingPaths, true)) {
                    return;
                }

                GalleryImage::create([
                    'gallery_id' => $gallery->id,
                    'image' => $media->path,
                    'caption' => $media->caption ?: $media->title,
                    'sort_order' => $startOrder + (($index + 1) * 10),
                ]);

                $existingPaths[] = $media->path;
            });
    }

    private function syncExistingImages(Request $request, Gallery $gallery): void
    {
        $existingImages = $request->input('existing_images', []);

        foreach ($gallery->images as $image) {
            $payload = $existingImages[$image->id] ?? [];

            if (($payload['delete'] ?? null) === '1') {
                Storage::disk('public')->delete($image->image);

                if ($gallery->cover_image === $image->image) {
                    $gallery->update(['cover_image' => null]);
                }

                $image->delete();
                continue;
            }

            $image->update([
                'caption' => $payload['caption'] ?? null,
                'sort_order' => $payload['sort_order'] ?? $image->sort_order,
            ]);
        }
    }

    private function ensureCoverImage(Gallery $gallery): void
    {
        if ($gallery->cover_image) {
            return;
        }

        $firstImage = $gallery->images()->first();

        if ($firstImage) {
            $gallery->update(['cover_image' => $firstImage->image]);
        }
    }

    private function storeImage(Request $request, string $field, string $directory): ?string
    {
        return $this->uploadedOrSelectedImage($request, $field, $directory);
    }

    private function uniqueSlug(string $value, ?Gallery $gallery = null): string
    {
        return app(\App\Services\SlugService::class)->unique(Gallery::class, $value, $gallery?->id, 'slug', strtolower(class_basename(Gallery::class)));

        $baseSlug = Str::slug($value) ?: Str::random(8);
        $slug = $baseSlug;
        $counter = 2;

        while (Gallery::query()
            ->where('slug', $slug)
            ->when($gallery, fn ($query) => $query->whereKeyNot($gallery->id))
            ->exists()) {
            $slug = $baseSlug.'-'.$counter++;
        }

        return $slug;
    }

    private function unions()
    {
        return GuildUnion::query()->orderBy('title')->orderBy('name')->get();
    }
}
