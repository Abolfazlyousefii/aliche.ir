<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Rules\SafeImageUpload;
use App\Services\MediaLibraryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class MediaController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $media = Media::query()->with('uploader')
            ->when($search, fn ($q) => $q->where(fn ($q) => $q->where('title', 'like', "%$search%")->orWhere('original_name', 'like', "%$search%")->orWhere('alt_text', 'like', "%$search%")))
            ->when($request->query('mime'), fn ($q, $mime) => $q->where('mime_type', 'like', "$mime%"))
            ->orderBy('created_at', $request->query('sort') === 'oldest' ? 'asc' : 'desc')
            ->paginate(24)->withQueryString();

        return view('admin.media.index', compact('media', 'search'));
    }

    public function picker(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('search'));
        $type = strtolower((string) $request->query('type', 'all'));
        $sort = strtolower((string) $request->query('sort', 'newest'));
        $perPage = min(max($request->integer('per_page', 24), 1), 48);

        $query = Media::query()
            ->select([
                'id', 'file_name', 'original_name', 'path', 'mime_type', 'extension',
                'size', 'width', 'height', 'alt_text', 'title', 'caption',
                'uploaded_by', 'created_at',
            ])
            ->with(['uploader:id,name'])
            ->images()
            ->when($search, fn ($query) => $query->where(fn ($query) => $query
                ->where('title', 'like', "%{$search}%")
                ->orWhere('original_name', 'like', "%{$search}%")
                ->orWhere('alt_text', 'like', "%{$search}%")
                ->orWhere('caption', 'like', "%{$search}%")))
            ->when(in_array($type, ['webp', 'png', 'jpg', 'gif'], true), function ($query) use ($type) {
                $mimeTypes = $type === 'jpg' ? ['image/jpeg', 'image/jpg', 'image/pjpeg'] : ["image/{$type}"];

                $query->whereIn('mime_type', $mimeTypes);
            });

        match ($sort) {
            'oldest' => $query->orderBy('created_at')->orderBy('id'),
            'largest' => $query->orderByDesc('size')->orderByDesc('id'),
            'smallest' => $query->orderBy('size')->orderByDesc('id'),
            default => $query->orderByDesc('created_at')->orderByDesc('id'),
        };

        $media = $query->paginate($perPage);
        $items = $media->getCollection()->map(fn (Media $item) => $this->pickerItem($item))->values();

        return response()->json([
            'data' => $items,
            'items' => $items,
            'current_page' => $media->currentPage(),
            'last_page' => $media->lastPage(),
            'per_page' => $media->perPage(),
            'total' => $media->total(),
        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'files' => ['required', 'array'],
            'files.*' => ['bail', 'file', new SafeImageUpload, 'max:'.config('media.max_upload_kilobytes', 5120)],
        ], [
            'files.*.max' => 'حجم هر تصویر نباید بیشتر از ۵ مگابایت باشد.',
        ]);
        $stored = collect();
        $service = app(MediaLibraryService::class);

        try {
            foreach ($request->file('files', []) as $file) {
                $stored->push($service->storeImage(
                    $file,
                    'media/'.now()->format('Y/m'),
                    'public',
                    $request->user()?->id,
                    $request->input('title') ?: null,
                    $request->input('alt_text')
                ));
            }
        } catch (Throwable $exception) {
            $stored->each(function (Media $media) use ($service) {
                $service->deleteFiles($media);
                $media->delete();
            });
            report($exception);

            if ($request->expectsJson()) {
                return response()->json(['message' => 'پردازش تصویر انجام نشد؛ لطفاً فایل سالم دیگری انتخاب کنید.'], 422);
            }

            return back()->withErrors(['files' => 'پردازش تصویر انجام نشد؛ لطفاً فایل سالم دیگری انتخاب کنید.']);
        }

        if ($request->expectsJson()) {
            $stored = Media::query()
                ->with(['uploader:id,name'])
                ->whereKey($stored->pluck('id'))
                ->orderByDesc('id')
                ->get();

            return response()->json([
                'items' => $stored->map(fn (Media $media) => $this->pickerItem($media))->values(),
            ], 201);
        }

        return back()->with('success', 'رسانه‌ها با موفقیت آپلود شدند.');
    }

    private function pickerItem(Media $media): array
    {
        $title = $media->title ?: ($media->original_name ?: $media->file_name);

        return [
            'id' => $media->id,
            'url' => $media->url,
            'thumbnail' => $media->url,
            'title' => $title,
            'alt' => $media->alt_text ?: $title,
            'original_name' => $media->original_name ?: $media->file_name,
            'size' => (int) $media->size,
            'width' => $media->width ? (int) $media->width : null,
            'height' => $media->height ? (int) $media->height : null,
            'mime_type' => $media->mime_type,
            'uploaded_at' => $media->created_at?->toIso8601String(),
            'uploader' => $media->uploader?->name,
        ];
    }

    public function update(Request $request, Media $medium): RedirectResponse
    {
        $medium->update($request->validate(['alt_text' => ['nullable', 'string', 'max:190'], 'title' => ['nullable', 'string', 'max:190'], 'caption' => ['nullable', 'string', 'max:500'], 'description' => ['nullable', 'string', 'max:2000']]));

        return back()->with('success', 'اطلاعات رسانه ذخیره شد.');
    }

    public function destroy(Media $medium): RedirectResponse
    {
        if ($medium->inUse()) {
            return back()->with('error', 'این رسانه در محتوا استفاده شده و حذف نشد.');
        }
        $medium->delete();
        app(MediaLibraryService::class)->deleteFiles($medium);

        return back()->with('success', 'رسانه حذف شد.');
    }
}
