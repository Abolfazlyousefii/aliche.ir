<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Services\MediaLibraryService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MediaController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $media = Media::query()->with('uploader')
            ->when($search, fn($q)=>$q->where(fn($q)=>$q->where('title','like',"%$search%")->orWhere('original_name','like',"%$search%")->orWhere('alt_text','like',"%$search%")))
            ->when($request->query('mime'), fn($q,$mime)=>$q->where('mime_type','like',"$mime%"))
            ->orderBy('created_at', $request->query('sort') === 'oldest' ? 'asc' : 'desc')
            ->paginate(24)->withQueryString();
        return view('admin.media.index', compact('media','search'));
    }
    public function picker(Request $request)
    {
        $search = trim((string) $request->query('search'));

        $items = Media::query()
            ->images()
            ->when($search, fn ($query) => $query->where(fn ($query) => $query
                ->where('title', 'like', "%{$search}%")
                ->orWhere('original_name', 'like', "%{$search}%")
                ->orWhere('alt_text', 'like', "%{$search}%")))
            ->latest()
            ->take(48)
            ->get()
            ->map(fn (Media $media) => [
                'id' => $media->id,
                'title' => $media->title ?: $media->original_name,
                'alt' => $media->alt_text ?: ($media->title ?: $media->original_name),
                'url' => $media->url,
            ]);

        return response()->json(['items' => $items]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate(['files'=>['required','array'],'files.*'=>['image','mimes:jpg,jpeg,png,webp,gif','max:8192']]);
        $stored = collect($request->file('files', []))->map(fn ($file) => app(MediaLibraryService::class)->storeImage(
            $file,
            'media/'.now()->format('Y/m'),
            'public',
            $request->user()?->id,
            $request->input('title') ?: null,
            $request->input('alt_text')
        ));

        if ($request->expectsJson()) {
            return response()->json([
                'items' => $stored->map(fn (Media $media) => [
                    'id' => $media->id,
                    'title' => $media->title ?: $media->original_name,
                    'alt' => $media->alt_text ?: ($media->title ?: $media->original_name),
                    'url' => $media->url,
                ])->values(),
            ], 201);
        }

        return back()->with('success','رسانه‌ها با موفقیت آپلود شدند.');
    }
    public function update(Request $request, Media $medium): RedirectResponse
    {
        $medium->update($request->validate(['alt_text'=>['nullable','string','max:190'],'title'=>['nullable','string','max:190'],'caption'=>['nullable','string','max:500'],'description'=>['nullable','string','max:2000']]));
        return back()->with('success','اطلاعات رسانه ذخیره شد.');
    }
    public function destroy(Media $medium): RedirectResponse
    {
        if ($medium->inUse()) return back()->with('error','این رسانه در محتوا استفاده شده و حذف نشد.');
        if (! Str::startsWith($medium->path, ['assets/','http://','https://','/'])) Storage::disk($medium->disk ?: 'public')->delete($medium->path);
        $medium->delete();
        return back()->with('success','رسانه حذف شد.');
    }
}
