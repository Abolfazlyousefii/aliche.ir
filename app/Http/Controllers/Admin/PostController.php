<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePostRequest;
use App\Http\Requests\Admin\UpdatePostRequest;
use App\Models\Category;
use App\Models\GuildUnion;
use App\Models\Media;
use App\Models\Post;
use App\Services\MediaLibraryService;
use App\Services\SlugService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

class PostController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $search = mb_substr(trim((string) $request->query('search', '')), 0, 150);

        $requestedStatus = (string) $request->query('status', '');
        $status = in_array($requestedStatus, Post::STATUSES, true) ? $requestedStatus : '';

        $requestedType = (string) $request->query('type', '');
        $type = in_array($requestedType, Post::TYPES, true) ? $requestedType : '';

        $categoryId = $this->positiveInteger($request->query('category_id'));
        $unionId = $this->positiveInteger($request->query('union_id'));

        $homepagePositionLabels = Post::homepagePositionLabels();
        $requestedHomepagePosition = (string) $request->query('homepage_position', '');
        $homepagePosition = array_key_exists($requestedHomepagePosition, $homepagePositionLabels)
            ? $requestedHomepagePosition
            : '';

        $from = mb_substr(trim((string) $request->query('from', '')), 0, 30);
        $to = mb_substr(trim((string) $request->query('to', '')), 0, 30);
        $today = $request->boolean('today');

        $fromDate = $this->postFilterDate($from, false);
        $toDate = $this->postFilterDate($to, true);

        $filterError = null;

        if ($from !== '' && ! $fromDate) {
            $filterError = 'تاریخ شروع معتبر نیست. تاریخ را به صورت شمسی، مانند ۱۴۰۴/۰۶/۰۵، وارد کنید.';
        } elseif ($to !== '' && ! $toDate) {
            $filterError = 'تاریخ پایان معتبر نیست. تاریخ را به صورت شمسی، مانند ۱۴۰۴/۰۶/۰۵، وارد کنید.';
        } elseif (! $today && $fromDate && $toDate && $fromDate->gt($toDate)) {
            $filterError = 'تاریخ شروع نمی‌تواند بعد از تاریخ پایان باشد.';
        }

        $posts = Post::query()
            ->with(['category', 'union', 'author'])
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('title', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%")
                ->orWhere('excerpt', 'like', "%{$search}%")))
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($type !== '', fn ($query) => $query->where('type', $type))
            ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
            ->when($unionId, fn ($query) => $query->where('union_id', $unionId))
            ->when($homepagePosition !== '', fn ($query) => $query->where('homepage_position', $homepagePosition))
            ->when($today, fn ($query) => $query->published()->publishedOn(now()))
            ->when(! $today && ! $filterError && $fromDate, fn ($query) => $query->where('published_at', '>=', $fromDate))
            ->when(! $today && ! $filterError && $toDate, fn ($query) => $query->where('published_at', '<=', $toDate))
            ->when($filterError !== null, fn ($query) => $query->whereRaw('1 = 0'))
            ->orderBy('sort_order')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $statusCounts = Post::query()
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $todayPublishedCount = Post::query()->published()->publishedOn(now())->count();
        $topNewsCount = Post::query()->where('homepage_position', 'top')->count();
        $featuredNewsCount = Post::query()->where('homepage_position', 'featured')->count();

        $filterCategories = Category::query()
            ->active()
            ->where('type', 'news')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get(['id', 'title']);

        $filterUnions = GuildUnion::query()
            ->orderByDesc('is_active')
            ->orderBy('title')
            ->orderBy('name')
            ->get(['id', 'title', 'name', 'is_active']);

        $viewData = compact(
            'posts',
            'search',
            'status',
            'type',
            'categoryId',
            'unionId',
            'homepagePosition',
            'homepagePositionLabels',
            'from',
            'to',
            'today',
            'filterError',
            'statusCounts',
            'todayPublishedCount',
            'topNewsCount',
            'featuredNewsCount',
            'filterCategories',
            'filterUnions'
        );

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'html' => view('admin.posts.partials.results', $viewData)->render(),
                'url' => $request->fullUrl(),
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'total' => $posts->total(),
                'from' => $posts->firstItem(),
                'to' => $posts->lastItem(),
                'filter_error' => $filterError,
            ]);
        }

        return view('admin.posts.index', $viewData);
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

    private function positiveInteger(mixed $value): ?int
    {
        $value = filter_var($value, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);

        return $value === false ? null : $value;
    }

    private function postFilterDate(?string $value, bool $endOfDay): ?Carbon
    {
        if (blank($value)) {
            return null;
        }

        try {
            $converted = jalali_to_gregorian_datetime($value, $endOfDay);

            if (blank($converted)) {
                return null;
            }

            return Carbon::parse($converted);
        } catch (Throwable) {
            return null;
        }
    }
}
