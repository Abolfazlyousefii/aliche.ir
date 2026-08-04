<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Concerns\SelectsMedia;
use App\Http\Requests\Admin\StoreAnnouncementRequest;
use App\Http\Requests\Admin\UpdateAnnouncementRequest;
use App\Models\Announcement;
use App\Models\AnnouncementCategory;
use App\Models\Media;
use App\Models\GuildUnion;
use App\Services\SlugService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    use SelectsMedia;
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $status = (string) $request->query('status', '');
        $unionId = $request->query('union_id');

        $announcements = Announcement::query()
            ->with(['category', 'union', 'author'])
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('title', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%")
                ->orWhere('excerpt', 'like', "%{$search}%")))
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($unionId, fn ($query) => $query->where('union_id', $unionId))
            ->orderBy('sort_order')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.announcements.index', [
            'announcements' => $announcements,
            'search' => $search,
            'status' => $status,
            'unionId' => $unionId,
            'unions' => $this->activeUnions(),
        ]);
    }

    public function create(): View
    {
        return view('admin.announcements.create', $this->formData(null));
    }

    public function store(StoreAnnouncementRequest $request): RedirectResponse
    {
        $data = $this->announcementData($request->validated());
        $data['created_by'] = $request->user()?->id;
        $data['featured_image'] = $this->storeFeaturedImage($request);
        $data['attachment'] = $this->storeAttachment($request);

        $announcement = Announcement::create($data);

        return redirect()->route('admin.announcements.show', $announcement)->with('success', 'اطلاعیه با موفقیت ایجاد شد.');
    }

    public function show(Announcement $announcement): View
    {
        $announcement->load(['category', 'union', 'author', 'approver']);

        return view('admin.announcements.show', compact('announcement'));
    }

    public function edit(Announcement $announcement): View
    {
        return view('admin.announcements.edit', $this->formData($announcement));
    }

    public function update(UpdateAnnouncementRequest $request, Announcement $announcement): RedirectResponse
    {
        $data = $this->announcementData($request->validated(), $announcement);

        if ($path = $this->storeFeaturedImage($request)) {
            if ($announcement->featured_image) {
                Storage::disk('public')->delete($announcement->featured_image);
            }

            $data['featured_image'] = $path;
        }

        if ((bool) $request->boolean('remove_attachment') && $announcement->attachment) {
            Storage::disk('public')->delete($announcement->attachment);
            $data['attachment'] = null;
        }

        if ($path = $this->storeAttachment($request)) {
            if ($announcement->attachment) {
                Storage::disk('public')->delete($announcement->attachment);
            }

            $data['attachment'] = $path;
        }

        $announcement->update($data);

        return redirect()->route('admin.announcements.show', $announcement)->with('success', 'اطلاعیه با موفقیت ویرایش شد.');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        if ($announcement->featured_image) {
            Storage::disk('public')->delete($announcement->featured_image);
        }

        if ($announcement->attachment) {
            Storage::disk('public')->delete($announcement->attachment);
        }

        $announcement->delete();

        return redirect()->route('admin.announcements.index')->with('success', 'اطلاعیه با موفقیت حذف شد.');
    }

    public function approve(Request $request, Announcement $announcement): RedirectResponse
    {
        $announcement->update([
            'status' => 'approved',
            'approved_by' => $request->user()?->id,
            'rejected_reason' => null,
        ]);

        return redirect()->route('admin.announcements.show', $announcement)->with('success', 'اطلاعیه تایید شد.');
    }

    public function publish(Request $request, Announcement $announcement): RedirectResponse
    {
        $announcement->update([
            'status' => 'published',
            'approved_by' => $request->user()?->id,
            'published_at' => $announcement->published_at ?: now(),
            'starts_at' => $announcement->starts_at ?: now(),
            'rejected_reason' => null,
        ]);

        return redirect()->route('admin.announcements.show', $announcement)->with('success', 'اطلاعیه تایید و منتشر شد.');
    }

    public function reject(Request $request, Announcement $announcement): RedirectResponse
    {
        $validated = $request->validate(['rejected_reason' => ['required', 'string', 'max:1000']]);

        $announcement->update([
            'status' => 'rejected',
            'approved_by' => $request->user()?->id,
            'rejected_reason' => $validated['rejected_reason'],
        ]);

        return redirect()->route('admin.announcements.show', $announcement)->with('success', 'اطلاعیه رد شد.');
    }

    /** @return array<string, mixed> */
    private function formData(?Announcement $announcement): array
    {
        return [
            'announcement' => $announcement,
            'statuses' => $this->allowedStatuses(),
            'categories' => AnnouncementCategory::query()->where('is_active', true)->orderBy('sort_order')->orderBy('title')->get(),
            'statusLabels' => Announcement::statusLabels(),
            'visibilityLabels' => Announcement::visibilityLabels(),
            'unions' => $this->activeUnions(),
            'mediaItems' => Media::query()->images()->latest()->limit(200)->get(),
        ];
    }

    /** @param array<string, mixed> $validated @return array<string, mixed> */
    private function announcementData(array $validated, ?Announcement $announcement = null): array
    {
        $validated = $this->sanitizeRichTextFields($validated, ['body', 'excerpt', 'short_description', 'description', 'content', 'footer_description', 'site_description']);

        $data = [
            'title' => $validated['title'],
            'slug' => app(SlugService::class)->unique(Announcement::class, ($validated['slug'] ?? '') ?: $validated['title'], $announcement?->id, 'slug', 'announcement'),
            'excerpt' => $validated['excerpt'] ?? null,
            'body' => $validated['body'] ?? null,
            'category_id' => $validated['category_id'] ?? null,
            'union_id' => $validated['union_id'] ?? null,
            'starts_at' => $validated['starts_at'] ?: null,
            'expires_at' => $validated['expires_at'] ?: null,
            'is_important' => (bool) $validated['is_important'],
            'show_on_home' => (bool) $validated['show_on_home'],
            'status' => $validated['status'],
            'visibility' => $validated['visibility'],
            'published_at' => $validated['published_at'] ?: null,
            'rejected_reason' => $validated['rejected_reason'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => (bool) $validated['is_active'],
        ];

        if (in_array($data['status'], ['approved', 'published'], true)) {
            $data['approved_by'] = auth()->id() ?: $announcement?->approved_by;
            $data['rejected_reason'] = null;
        }

        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        if ($data['status'] === 'published' && empty($data['starts_at'])) {
            $data['starts_at'] = now();
        }

        return $data;
    }

    private function storeFeaturedImage(Request $request): ?string
    {
        return $this->uploadedOrSelectedImage($request, 'featured_image', 'announcements/featured');
    }

    private function storeAttachment(Request $request): ?string
    {
        return $request->hasFile('attachment')
            ? $request->file('attachment')->store('announcements/attachments', 'public')
            : null;
    }

    private function activeUnions()
    {
        return GuildUnion::query()->where('is_active', true)->orderBy('title')->orderBy('name')->get();
    }

    /** @return array<int, string> */
    private function allowedStatuses(): array
    {
        $statuses = Announcement::LIMITED_STATUSES;

        if (request()->user()?->hasPermission('announcements.approve')) {
            $statuses = array_merge($statuses, ['approved', 'rejected', 'archived']);
        }

        if (request()->user()?->hasPermission('announcements.publish')) {
            $statuses[] = 'published';
        }

        return array_values(array_unique($statuses));
    }
}
