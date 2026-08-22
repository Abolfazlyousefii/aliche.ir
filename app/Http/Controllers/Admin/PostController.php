<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePostRequest;
use App\Http\Requests\Admin\UpdatePostRequest;
use App\Models\Category;
use App\Models\GuildUnion;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Media;
use App\Services\MediaLibraryService;
use App\Services\SlugService;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $status = (string) $request->query('status', '');
        $type = (string) $request->query('type', '');
        $categoryId = $request->query('category_id');
        $authorId = $request->query('author_id');
        $from = $request->query('from');
        $to = $request->query('to');
        $today = $request->boolean('today');

        $posts = Post::query()
            ->with(['category', 'union', 'author'])
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('title', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%")
                ->orWhere('excerpt', 'like', "%{$search}%")))
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($type !== '', fn ($query) => $query->where('type', $type))
            ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
            ->when($authorId, fn ($query) => $query->where('created_by', $authorId))
            ->when($request->query('homepage_position'), fn ($query, $position) => $query->where('homepage_position', $position))
            ->when($today, fn ($query) => $query->published()->publishedOn(now()))
            ->when($from, fn ($query) => $query->whereDate('published_at', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('published_at', '<=', $to))
            ->orderBy('sort_order')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $statusCounts = Post::query()->selectRaw('status, count(*) as aggregate')->groupBy('status')->pluck('aggregate', 'status');
        $todayPublishedCount = Post::query()->published()->publishedOn(now())->count();
        return view('admin.posts.index', compact('posts', 'search', 'status', 'type', 'statusCounts', 'todayPublishedCount')); 
    }

    public function create(): View
    {
        return view('admin.posts.create', $this->formData(null));
    }

    public function store(StorePostRequest $request): RedirectResponse
    {
        $data = $this->postData($request->validated());
        $data['created_by'] = $request->user()?->id;
        $data['featured_image'] = $this->storeFeaturedImage($request);

        $post = Post::create($data);
        $this->storeGalleryImages($request, $post);
        $this->syncMediaGallery($request, $post);
        $this->flushFrontendCache();

        return redirect()->route('admin.posts.show', $post)->with('success', 'خبر با موفقیت ایجاد شد.');
    }

    public function show(Post $post): View
    {
        $post->load(['category', 'union', 'author', 'approver', 'galleries']);

        return view('admin.posts.show', compact('post'));
    }

    public function edit(Post $post): View
    {
        $post->load(['galleries', 'featuredMedia', 'mediaGallery']);

        return view('admin.posts.edit', $this->formData($post));
    }

    public function update(UpdatePostRequest $request, Post $post): RedirectResponse
    {
        $data = $this->postData($request->validated(), $post);

        if ($path = $this->storeFeaturedImage($request)) {
            if ($post->featured_image) {
                Storage::disk('public')->delete($post->featured_image);
            }

            $data['featured_image'] = $path;
        }

        $post->update($data);
        $this->flushFrontendCache();
        $this->deleteSelectedGalleryImages($request, $post);
        $this->storeGalleryImages($request, $post);
        $this->syncMediaGallery($request, $post);

        return redirect()->route('admin.posts.show', $post)->with('success', 'خبر با موفقیت ویرایش شد.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        if ($post->featured_image) {
            Storage::disk('public')->delete($post->featured_image);
        }

        foreach ($post->galleries as $gallery) {
            Storage::disk('public')->delete($gallery->image);
        }

        $post->delete();
        $this->flushFrontendCache();

        return redirect()->route('admin.posts.index')->with('success', 'خبر با موفقیت حذف شد.');
    }

    public function approve(Request $request, Post $post): RedirectResponse
    {
        if (! $post->canBeApproved()) {
            return redirect()->route('admin.posts.show', $post)->with('error', 'این خبر در وضعیت فعلی قابل تایید نیست.');
        }

        $post->update([
            'status' => 'approved',
            'approved_by' => $request->user()?->id,
            'rejected_reason' => null,
        ]);
        $this->flushFrontendCache();

        return redirect()->route('admin.posts.show', $post)->with('success', 'خبر تایید شد و اکنون آماده انتشار است.');
    }

    public function publish(Request $request, Post $post): RedirectResponse
    {
        if (! $post->canBePublished()) {
            return redirect()->route('admin.posts.show', $post)->with('error', 'این خبر در وضعیت فعلی قابل تایید و انتشار نیست.');
        }

        $post->update([
            'status' => 'published',
            'approved_by' => $request->user()?->id,
            'published_at' => $post->published_at ?: now(),
            'rejected_reason' => null,
        ]);
        $this->flushFrontendCache();

        return redirect()->route('admin.posts.show', $post)->with('success', 'خبر تایید و منتشر شد و در سایت قابل نمایش است.');
    }

    public function unpublish(Post $post): RedirectResponse
    {
        if (! $post->canBeUnpublished()) {
            return redirect()->route('admin.posts.show', $post)->with('error', 'این خبر در وضعیت فعلی منتشرشده نیست.');
        }

        $post->update(['status' => 'draft']);
        $this->flushFrontendCache();

        return redirect()->route('admin.posts.show', $post)->with('success', 'انتشار خبر لغو شد و وضعیت آن به پیش‌نویس تغییر کرد.');
    }

    public function reject(Request $request, Post $post): RedirectResponse
    {
        if (! $post->canBeRejected()) {
            return redirect()->route('admin.posts.show', $post)->with('error', 'این خبر در وضعیت فعلی قابل رد نیست.');
        }

        $validated = $request->validate(['rejected_reason' => ['required', 'string', 'max:1000']]);

        $post->update([
            'status' => 'rejected',
            'approved_by' => $request->user()?->id,
            'rejected_reason' => $validated['rejected_reason'],
        ]);
        $this->flushFrontendCache();

        return redirect()->route('admin.posts.show', $post)->with('success', 'خبر رد شد.');
    }

    /** @return array<string, mixed> */
    private function formData(?Post $post): array
    {
        return [
            'post' => $post,
            'statuses' => $this->allowedStatuses(),
            'types' => $post && in_array($post->type, Post::LEGACY_TYPES, true)
                ? array_merge(Post::TYPES, [$post->type])
                : Post::TYPES,
            'typeLabels' => Post::typeLabels(),
            'statusLabels' => Post::statusLabels(),
            'categories' => Category::query()->active()->where('type', 'news')->orderBy('sort_order')->orderBy('title')->get(),
            'mediaItems' => Media::query()->latest()->take(60)->get(),
            'homepagePositionLabels' => Post::homepagePositionLabels(),
            'unions' => GuildUnion::query()->orderByDesc('is_active')->orderBy('title')->orderBy('name')->get(),
        ];
    }

    /** @param array<string, mixed> $validated @return array<string, mixed> */
    private function postData(array $validated, ?Post $post = null): array
    {
        $validated = $this->sanitizeRichTextFields($validated, ['body', 'excerpt', 'short_description', 'description', 'content', 'footer_description', 'site_description']);

        $data = [
            'title' => $validated['title'],
            'slug' => app(SlugService::class)->unique(Post::class, $validated['slug'] ?: $validated['title'], $post?->id, 'slug', 'post'),
            'excerpt' => $validated['excerpt'] ?? null,
            'body' => $validated['body'] ?? null,
            'category_id' => filled($validated['category_id'] ?? null) ? $validated['category_id'] : null,
            'union_id' => filled($validated['union_id'] ?? null) ? $validated['union_id'] : null,
            'type' => $validated['type'],
            'homepage_position' => $validated['homepage_position'] ?? 'normal',
            'is_important' => (bool) ($validated['is_important'] ?? false),
            'is_featured' => ($validated['homepage_position'] ?? 'normal') === 'featured',
            'featured_order' => $validated['featured_order'] ?? ($validated['sort_order'] ?? 0),
            'is_top' => ($validated['homepage_position'] ?? 'normal') === 'top',
            'status' => $validated['status'],
            'published_at' => $validated['published_at'] ?: ($post?->published_at ?: ($post ? null : now())),
            'rejected_reason' => $validated['rejected_reason'] ?? null,
            'meta_title' => $validated['meta_title'] ?? null,
            'meta_description' => $validated['meta_description'] ?? null,
            'meta_keywords' => ! empty($validated['meta_keywords'])
                ? collect($validated['meta_keywords'])->map(fn ($keyword) => trim((string) $keyword))->filter()->unique()->implode(', ')
                : null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => true,
            'featured_media_id' => $validated['featured_media_id'] ?? null,
        ];

        if (in_array($data['status'], ['approved', 'published'], true)) {
            $data['approved_by'] = auth()->id() ?: $post?->approved_by;
            $data['rejected_reason'] = null;
        }

        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        return $data;
    }

    private function storeFeaturedImage(Request $request): ?string
    {
        if (! $request->hasFile('featured_image')) {
            return null;
        }

        return app(MediaLibraryService::class)->storeImage(
            $request->file('featured_image'),
            'posts/featured',
            'public',
            $request->user()?->id
        )->path;
    }

    private function storeGalleryImages(Request $request, Post $post): void
    {
        if (! $request->hasFile('gallery_images')) {
            return;
        }

        $captions = $request->input('gallery_captions', []);
        $nextSort = (int) $post->galleries()->max('sort_order') + 1;

        foreach ($request->file('gallery_images') as $index => $image) {
            $post->galleries()->create([
                'image' => app(MediaLibraryService::class)->storeImage($image, 'posts/gallery', 'public', $request->user()?->id)->path,
                'caption' => $captions[$index] ?? null,
                'sort_order' => $nextSort + $index,
            ]);
        }
    }

    private function syncMediaGallery(Request $request, Post $post): void
    {
        $ids = collect($request->input('gallery_media_ids', []))->filter()->unique()->values();
        $sync = [];

        foreach ($ids as $i => $id) {
            $sync[(int) $id] = ['sort_order' => $i + 1];
        }

        $post->mediaGallery()->sync($sync);
    }

    private function deleteSelectedGalleryImages(Request $request, Post $post): void
    {
        $ids = collect($request->input('delete_gallery', []))->filter()->values();

        if ($ids->isEmpty()) {
            return;
        }

        $post->galleries()->whereIn('id', $ids)->get()->each(function ($gallery) {
            Storage::disk('public')->delete($gallery->image);
            $gallery->delete();
        });
    }

    private function flushFrontendCache(): void
    {
        Cache::forget('settings.all');
        app(\App\Services\DailyNewsService::class)->forgetFor(now()->startOfDay());
    }

    /** @return array<int, string> */
    private function allowedStatuses(): array
    {
        $statuses = Post::LIMITED_STATUSES;

        if (request()->user()?->hasPermission('posts.approve')) {
            $statuses = array_merge($statuses, ['approved', 'rejected', 'archived']);
        }

        if (request()->user()?->hasPermission('posts.publish')) {
            $statuses[] = 'published';
        }

        return array_values(array_unique($statuses));
    }
}
