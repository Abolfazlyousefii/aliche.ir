<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Concerns\SelectsMedia;
use App\Models\GuildUnion;
use App\Models\Category;
use App\Models\Video;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class VideoController extends Controller
{
    use SelectsMedia;
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $status = (string) $request->query('status', '');
        $videoType = (string) $request->query('video_type', '');
        $unionId = $request->query('union_id');

        $videos = Video::query()
            ->with(['union', 'creator'])
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('title', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")))
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($videoType !== '', fn ($query) => $query->where('video_type', $videoType))
            ->when($unionId, fn ($query) => $query->where('union_id', $unionId))
            ->orderBy('sort_order')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.videos.index', [
            'videos' => $videos,
            'search' => $search,
            'status' => $status,
            'videoType' => $videoType,
            'unionId' => $unionId,
            'unions' => $this->unions(),
            'categories' => Category::query()->active()->where('type', 'video')->orderBy('sort_order')->orderBy('title')->get(),
            'statusLabels' => Video::statusLabels(),
            'typeLabels' => Video::typeLabels(),
        ]);
    }

    public function create(): View
    {
        return view('admin.videos.create', [
            'video' => null,
            'unions' => $this->unions(),
            'categories' => Category::query()->active()->where('type', 'video')->orderBy('sort_order')->orderBy('title')->get(),
            'statusLabels' => Video::statusLabels(),
            'typeLabels' => Video::typeLabels(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->sanitizeRichTextFields($this->validatedData($request), ['body', 'excerpt', 'short_description', 'description', 'content', 'footer_description', 'site_description']);
        $this->validateVideoSource($request, $validated);
        $status = $validated['status'];

        $video = Video::create([
            ...$this->videoData($validated),
            'cover_image' => $this->storeFile($request, 'cover_image', 'videos/covers', 'public'),
            'video_file' => $validated['video_type'] === 'upload' ? $this->storeFile($request, 'video_file', 'videos/files', 'public') : null,
            'created_by' => $request->user()->id,
            'approved_by' => in_array($status, ['approved', 'published'], true) ? $request->user()->id : null,
        ]);

        return redirect()->route('admin.videos.show', $video)->with('success', 'ویدیو با موفقیت ایجاد شد.');
    }

    public function show(Video $video): View
    {
        $video->load(['union', 'creator', 'approver']);

        return view('admin.videos.show', compact('video'));
    }

    public function edit(Video $video): View
    {
        return view('admin.videos.edit', [
            'video' => $video,
            'unions' => $this->unions(),
            'categories' => Category::query()->active()->where('type', 'video')->orderBy('sort_order')->orderBy('title')->get(),
            'statusLabels' => Video::statusLabels(),
            'typeLabels' => Video::typeLabels(),
        ]);
    }

    public function update(Request $request, Video $video): RedirectResponse
    {
        $validated = $this->sanitizeRichTextFields($this->validatedData($request, $video), ['body', 'excerpt', 'short_description', 'description', 'content', 'footer_description', 'site_description']);
        $this->validateVideoSource($request, $validated, $video);
        $data = $this->videoData($validated, $video);

        if ($coverImage = $this->storeFile($request, 'cover_image', 'videos/covers', 'public')) {
            if ($video->cover_image) {
                Storage::disk('public')->delete($video->cover_image);
            }

            $data['cover_image'] = $coverImage;
        }

        if ($validated['video_type'] === 'upload') {
            $data['aparat_url'] = null;

            if ($videoFile = $this->storeFile($request, 'video_file', 'videos/files', 'public')) {
                if ($video->video_file) {
                    Storage::disk('public')->delete($video->video_file);
                }

                $data['video_file'] = $videoFile;
            }
        } else {
            if ($video->video_file) {
                Storage::disk('public')->delete($video->video_file);
            }

            $data['video_file'] = null;
        }

        if (in_array($validated['status'], ['approved', 'published'], true) && ! $video->approved_by) {
            $data['approved_by'] = $request->user()->id;
        }

        $video->update($data);

        return redirect()->route('admin.videos.show', $video)->with('success', 'ویدیو با موفقیت ویرایش شد.');
    }

    public function destroy(Video $video): RedirectResponse
    {
        if ($video->cover_image) {
            Storage::disk('public')->delete($video->cover_image);
        }

        if ($video->video_file) {
            Storage::disk('public')->delete($video->video_file);
        }

        $video->delete();

        return redirect()->route('admin.videos.index')->with('success', 'ویدیو با موفقیت حذف شد.');
    }

    private function validatedData(Request $request, ?Video $video = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('videos', 'slug')->ignore($video?->id)],
            'description' => ['nullable', 'string'],
            'cover_image' => ['nullable', 'image', 'max:4096'],
            'video_type' => ['required', Rule::in(Video::VIDEO_TYPES)],
            'video_file' => ['nullable', 'file', 'mimes:mp4,mov,avi,wmv,webm,mkv', 'max:102400'],
            'aparat_url' => ['nullable', 'url', 'max:500'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'union_id' => ['nullable', 'exists:unions,id'],
            'status' => ['required', Rule::in(app(\App\Services\ContentApprovalService::class)->allowedStatusesFor($request->user(), ['videos.approve', 'videos.publish']))],
            'published_at' => ['nullable', 'date'],
            'rejected_reason' => ['nullable', 'required_if:status,rejected', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['required', Rule::in(['0', '1'])],
        ], [], [
            'title' => 'عنوان',
            'slug' => 'نامک',
            'description' => 'توضیحات',
            'cover_image' => 'تصویر کاور',
            'video_type' => 'نوع ویدیو',
            'video_file' => 'فایل ویدیو',
            'aparat_url' => 'لینک آپارات',
            'category_id' => 'دسته‌بندی',
            'union_id' => 'اتحادیه',
            'status' => 'وضعیت',
            'published_at' => 'تاریخ انتشار',
            'rejected_reason' => 'دلیل رد',
            'sort_order' => 'ترتیب نمایش',
            'is_active' => 'فعال',
        ]);
    }

    private function validateVideoSource(Request $request, array $validated, ?Video $video = null): void
    {
        if ($validated['video_type'] === 'upload' && ! $request->hasFile('video_file') && ! $video?->video_file) {
            throw ValidationException::withMessages(['video_file' => 'برای نوع آپلود مستقیم، انتخاب فایل ویدیو الزامی است.']);
        }

        if ($validated['video_type'] === 'aparat' && blank($validated['aparat_url'] ?? null)) {
            throw ValidationException::withMessages(['aparat_url' => 'برای نوع آپارات، وارد کردن لینک آپارات الزامی است.']);
        }
    }

    private function videoData(array $validated, ?Video $video = null): array
    {
        $validated = $this->sanitizeRichTextFields($validated, ['body', 'excerpt', 'short_description', 'description', 'content', 'footer_description', 'site_description']);

        $status = $validated['status'];
        $publishedAt = $validated['published_at'] ?? null;

        if ($status === 'published' && ! $publishedAt) {
            $publishedAt = $video?->published_at ?: now();
        }

        return [
            'title' => $validated['title'],
            'slug' => $this->uniqueSlug($validated['slug'] ?: $validated['title'], $video),
            'description' => $validated['description'] ?? null,
            'video_type' => $validated['video_type'],
            'aparat_url' => $validated['video_type'] === 'aparat' ? ($validated['aparat_url'] ?? null) : null,
            'category_id' => $validated['category_id'] ?? null,
            'union_id' => $validated['union_id'] ?? null,
            'status' => $status,
            'published_at' => $publishedAt,
            'rejected_reason' => $validated['rejected_reason'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => (bool) $validated['is_active'],
        ];
    }

    private function storeFile(Request $request, string $field, string $directory, string $disk): ?string
    {
        return $field === 'cover_image'
            ? $this->uploadedOrSelectedImage($request, $field, $directory, $disk)
            : ($request->hasFile($field) ? $request->file($field)->store($directory, $disk) : null);
    }

    private function uniqueSlug(string $value, ?Video $video = null): string
    {
        return app(\App\Services\SlugService::class)->unique(Video::class, $value, $video?->id, 'slug', strtolower(class_basename(Video::class)));

        $baseSlug = Str::slug($value) ?: Str::random(8);
        $slug = $baseSlug;
        $counter = 2;

        while (Video::query()
            ->where('slug', $slug)
            ->when($video, fn ($query) => $query->whereKeyNot($video->id))
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
