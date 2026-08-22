<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Concerns\SelectsMedia;
use App\Http\Requests\Admin\StorePageRequest;
use App\Http\Requests\Admin\UpdatePageRequest;
use App\Models\Media;
use App\Models\Page;
use App\Services\SlugService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PageController extends Controller
{
    use SelectsMedia;
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $status = (string) $request->query('status', '');

        $pages = Page::query()
            ->with('author')
            ->when($search !== '', fn($query) => $query->where(fn($query) => $query
                ->where('title', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%")))
            ->when($status !== '', fn($query) => $query->where('status', $status))
            ->orderBy('sort_order')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.pages.index', compact('pages', 'search', 'status'));
    }

    public function create(): View
    {
        return view('admin.pages.create', $this->formData(null));
    }

    public function store(StorePageRequest $request): RedirectResponse
    {
        $data = $this->pageData($request->validated());
        $data['created_by'] = $request->user()?->id;
        $data['featured_image'] = $this->storeFeaturedImage($request);

        $page = Page::create($data);

        return redirect()->route('admin.pages.show', $page)->with('success', 'صفحه با موفقیت ایجاد شد.');
    }

    public function show(Page $page): View
    {
        $page->load(['author', 'approver']);

        return view('admin.pages.show', compact('page'));
    }

    public function edit(Page $page): View
    {
        return view('admin.pages.edit', $this->formData($page));
    }

    public function update(UpdatePageRequest $request, Page $page): RedirectResponse
    {
        $data = $this->pageData($request->validated(), $page);

        if ($path = $this->storeFeaturedImage($request)) {
            if ($page->featured_image) {
                Storage::disk('public')->delete($page->featured_image);
            }

            $data['featured_image'] = $path;
        }

        $page->update($data);

        return redirect()->route('admin.pages.show', $page)->with('success', 'صفحه با موفقیت ویرایش شد.');
    }

    public function destroy(Page $page): RedirectResponse
    {
        if ($page->featured_image) {
            Storage::disk('public')->delete($page->featured_image);
        }

        $page->delete();

        return redirect()->route('admin.pages.index')->with('success', 'صفحه با موفقیت حذف شد.');
    }

    public function approve(Request $request, Page $page): RedirectResponse
    {
        $page->update([
            'status' => 'approved',
            'approved_by' => $request->user()?->id,
            'rejected_reason' => null,
        ]);

        return redirect()->route('admin.pages.show', $page)->with('success', 'صفحه تایید شد.');
    }

    public function publish(Request $request, Page $page): RedirectResponse
    {
        $page->update([
            'status' => 'published',
            'approved_by' => $request->user()?->id,
            'published_at' => $page->published_at ?: now(),
            'rejected_reason' => null,
        ]);

        return redirect()->route('admin.pages.show', $page)->with('success', 'صفحه منتشر شد.');
    }

    public function reject(Request $request, Page $page): RedirectResponse
    {
        $validated = $request->validate(['rejected_reason' => ['required', 'string', 'max:1000']]);

        $page->update([
            'status' => 'rejected',
            'approved_by' => $request->user()?->id,
            'rejected_reason' => $validated['rejected_reason'],
        ]);

        return redirect()->route('admin.pages.show', $page)->with('success', 'صفحه رد شد.');
    }

    /** @return array<string, mixed> */
    private function formData(?Page $page): array
    {
        return [
            'page' => $page,
            'statuses' => $this->allowedStatuses(),
            'templates' => Page::TEMPLATES,
            'templateLabels' => Page::templateLabels(),
            'statusLabels' => Page::statusLabels(),
            'mediaItems' => Media::query()->images()->latest()->take(60)->get(),
        ];
    }

    /** @param array<string, mixed> $validated @return array<string, mixed> */
    private function pageData(array $validated, ?Page $page = null): array
    {
        $validated = $this->sanitizeRichTextFields($validated, ['body', 'excerpt', 'short_description', 'description', 'content', 'footer_description', 'site_description']);

        $data = [
            'title' => $validated['title'],
            'slug' => app(SlugService::class)->unique(Page::class, ($validated['slug'] ?? '') ?: $validated['title'], $page?->id, 'slug', 'page'),
            'excerpt' => $validated['excerpt'] ?? null,
            'body' => $validated['body'] ?? null,
            'template' => $validated['template'],
            'meta_title' => $validated['meta_title'] ?? null,
            'meta_description' => $validated['meta_description'] ?? null,
            'meta_keywords' => $validated['meta_keywords'] ?? null,
            'status' => $validated['status'],
            'published_at' => $validated['published_at'] ?? null,
            'rejected_reason' => $validated['rejected_reason'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => (bool) $validated['is_active'],
        ];

        if (in_array($data['status'], ['approved', 'published'], true)) {
            $data['approved_by'] = auth()->id() ?: $page?->approved_by;
        }

        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        return $data;
    }

    private function storeFeaturedImage(Request $request): ?string
    {
        return $this->uploadedOrSelectedImage($request, 'featured_image', 'pages');
    }

    /** @return array<int, string> */
    private function allowedStatuses(): array
    {
        return request()->user()?->hasPermission('pages.approve') ? Page::STATUSES : Page::LIMITED_STATUSES;
    }
}
