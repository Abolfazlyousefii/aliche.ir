<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Concerns\SelectsMedia;
use App\Models\Category;
use App\Models\System;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SystemController extends Controller
{
    use SelectsMedia;
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $categoryId = $request->query('category_id');
        $status = (string) $request->query('status', '');

        $systems = System::query()
            ->with('category')
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('title', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%")
                ->orWhere('short_description', 'like', "%{$search}%")
                ->orWhere('link', 'like', "%{$search}%")))
            ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->orderBy('sort_order')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.systems.index', [
            'systems' => $systems,
            'categories' => $this->categories(),
            'targetLabels' => System::targetLabels(),
            'statusLabels' => System::statusLabels(),
            'search' => $search,
            'categoryId' => $categoryId,
            'status' => $status,
        ]);
    }

    public function create(): View
    {
        return view('admin.systems.create', [
            'categories' => $this->categories(),
            'targetLabels' => System::targetLabels(),
            'statusLabels' => System::statusLabels(),
            'iconPresets' => System::iconPresets(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->sanitizeRichTextFields($this->validatedData($request), ['body', 'excerpt', 'short_description', 'description', 'content', 'footer_description', 'site_description']);

        System::create([
            ...$this->systemData($validated),
            'created_by' => $request->user()?->id,
            'image' => $this->uploadedOrSelectedImage($request, 'image', 'systems'),
        ]);

        return redirect()->route('admin.systems.index')->with('success', 'سامانه با موفقیت ایجاد شد.');
    }

    public function show(System $system): View
    {
        $system->load(['category', 'creator', 'approver']);

        return view('admin.systems.show', compact('system'));
    }

    public function edit(System $system): View
    {
        return view('admin.systems.edit', [
            'system' => $system,
            'categories' => $this->categories(),
            'targetLabels' => System::targetLabels(),
            'statusLabels' => System::statusLabels(),
            'iconPresets' => System::iconPresets(),
        ]);
    }

    public function update(Request $request, System $system): RedirectResponse
    {
        $validated = $this->sanitizeRichTextFields($this->validatedData($request, $system), ['body', 'excerpt', 'short_description', 'description', 'content', 'footer_description', 'site_description']);
        $data = $this->systemData($validated, $system);

        if ($image = $this->uploadedOrSelectedImage($request, 'image', 'systems')) {
            if ($system->image) {
                Storage::disk('public')->delete($system->image);
            }

            $data['image'] = $image;
        }

        $system->update($data);

        return redirect()->route('admin.systems.show', $system)->with('success', 'سامانه با موفقیت ویرایش شد.');
    }

    public function destroy(System $system): RedirectResponse
    {
        if ($system->image) {
            Storage::disk('public')->delete($system->image);
        }

        $system->delete();

        return redirect()->route('admin.systems.index')->with('success', 'سامانه با موفقیت حذف شد.');
    }

    private function validatedData(Request $request, ?System $system = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('systems', 'slug')->ignore($system?->id)],
            'description' => ['nullable', 'string'],
            'short_description' => ['nullable', 'string', 'max:1000'],
            'icon' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'max:4096'],
            'link' => ['nullable', 'url', 'max:500'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'target' => ['required', Rule::in(System::TARGETS)],
            'status' => ['required', Rule::in(app(\App\Services\ContentApprovalService::class)->allowedStatusesFor($request->user(), ['systems.approve', 'systems.publish']))],
            'published_at' => ['nullable', 'date'],
            'rejected_reason' => ['nullable', 'required_if:status,rejected', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['required', Rule::in(['0', '1'])],
        ], [], [
            'title' => 'عنوان',
            'slug' => 'نامک',
            'description' => 'توضیحات',
            'short_description' => 'توضیح کوتاه',
            'icon' => 'آیکن',
            'image' => 'تصویر',
            'link' => 'لینک',
            'category_id' => 'دسته‌بندی',
            'target' => 'نحوه باز شدن لینک',
            'status' => 'وضعیت',
            'published_at' => 'تاریخ انتشار',
            'rejected_reason' => 'دلیل رد',
            'sort_order' => 'ترتیب نمایش',
            'is_active' => 'فعال',
        ]);
    }

    private function systemData(array $validated, ?System $system = null): array
    {
        $validated = $this->sanitizeRichTextFields($validated, ['body', 'excerpt', 'short_description', 'description', 'content', 'footer_description', 'site_description']);

        return [
            'title' => $validated['title'],
            'slug' => $this->uniqueSlug($validated['slug'] ?: $validated['title'], $system),
            'description' => $validated['description'] ?? null,
            'short_description' => $validated['short_description'] ?? null,
            'icon' => $validated['icon'] ?? null,
            'link' => $validated['link'] ?? null,
            'category_id' => $validated['category_id'] ?? null,
            'target' => $validated['target'],
            'status' => $validated['status'],
            'published_at' => ($validated['status'] === 'published' && empty($validated['published_at'])) ? now() : ($validated['published_at'] ?? null),
            'rejected_reason' => $validated['rejected_reason'] ?? null,
            'approved_by' => in_array($validated['status'], ['approved', 'published'], true) ? (auth()->id() ?: $system?->approved_by) : $system?->approved_by,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => (bool) $validated['is_active'],
        ];
    }

    private function uniqueSlug(string $value, ?System $system = null): string
    {
        return app(\App\Services\SlugService::class)->unique(System::class, $value, $system?->id, 'slug', strtolower(class_basename(System::class)));

        $baseSlug = Str::slug($value) ?: Str::random(8);
        $slug = $baseSlug;
        $counter = 2;

        while (System::query()
            ->where('slug', $slug)
            ->when($system, fn ($query) => $query->whereKeyNot($system->id))
            ->exists()) {
            $slug = $baseSlug.'-'.$counter++;
        }

        return $slug;
    }

    private function categories()
    {
        return Category::query()->active()->where('type', 'system')->orderBy('sort_order')->orderBy('title')->get();
    }
}
