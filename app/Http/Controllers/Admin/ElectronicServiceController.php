<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\SelectsMedia;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ElectronicService;
use App\Rules\SafeImageUpload;
use App\Services\ContentApprovalService;
use App\Services\SlugService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ElectronicServiceController extends Controller
{
    use SelectsMedia;

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $categoryId = $request->query('category_id');
        $status = (string) $request->query('status', '');

        $services = ElectronicService::query()
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

        return view('admin.electronic_services.index', [
            'services' => $services,
            'categories' => $this->categories(),
            'statusLabels' => ElectronicService::statusLabels(),
            'search' => $search,
            'categoryId' => $categoryId,
            'status' => $status,
        ]);
    }

    public function create(): View
    {
        return view('admin.electronic_services.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->sanitizeRichTextFields($this->validatedData($request), ['body', 'excerpt', 'short_description', 'description', 'content', 'footer_description', 'site_description']);

        $service = ElectronicService::create([
            ...$this->serviceData($validated),
            'created_by' => $request->user()?->id,
            'image' => $this->uploadedOrSelectedImage($request, 'image', 'electronic-services'),
        ]);

        return redirect()->route('admin.electronic_services.show', $service)->with('success', 'خدمت الکترونیک با موفقیت ایجاد شد.');
    }

    public function show(ElectronicService $electronicService): View
    {
        $electronicService->load(['category', 'creator', 'approver']);

        return view('admin.electronic_services.show', ['service' => $electronicService]);
    }

    public function edit(ElectronicService $electronicService): View
    {
        return view('admin.electronic_services.edit', [
            ...$this->formData(),
            'service' => $electronicService,
        ]);
    }

    public function update(Request $request, ElectronicService $electronicService): RedirectResponse
    {
        $validated = $this->sanitizeRichTextFields($this->validatedData($request, $electronicService), ['body', 'excerpt', 'short_description', 'description', 'content', 'footer_description', 'site_description']);
        $data = $this->serviceData($validated, $electronicService);

        if ($image = $this->uploadedOrSelectedImage($request, 'image', 'electronic-services')) {
            if ($electronicService->image) {
                Storage::disk('public')->delete($electronicService->image);
            }

            $data['image'] = $image;
        }

        $electronicService->update($data);

        return redirect()->route('admin.electronic_services.show', $electronicService)->with('success', 'خدمت الکترونیک با موفقیت ویرایش شد.');
    }

    public function destroy(ElectronicService $electronicService): RedirectResponse
    {
        if ($electronicService->image) {
            Storage::disk('public')->delete($electronicService->image);
        }

        $electronicService->delete();

        return redirect()->route('admin.electronic_services.index')->with('success', 'خدمت الکترونیک با موفقیت حذف شد.');
    }

    private function validatedData(Request $request, ?ElectronicService $service = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('electronic_services', 'slug')->ignore($service?->id)],
            'short_description' => ['nullable', 'string', 'max:1000'],
            'body' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'bail', 'file', new SafeImageUpload, 'max:'.config('media.max_upload_kilobytes', 5120)],
            'link_type' => ['required', Rule::in(ElectronicService::LINK_TYPES)],
            'link' => ['nullable', 'required_unless:link_type,none', 'string', 'max:500'],
            'target' => ['required', Rule::in(ElectronicService::TARGETS)],
            'category_id' => ['nullable', 'exists:categories,id'],
            'status' => ['required', Rule::in(app(ContentApprovalService::class)->allowedStatusesFor($request->user(), ['electronic_services.approve', 'electronic_services.publish']))],
            'published_at' => ['nullable', 'date'],
            'rejected_reason' => ['nullable', 'required_if:status,rejected', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['required', Rule::in(['0', '1'])],
        ], [], [
            'title' => 'عنوان',
            'slug' => 'نامک',
            'short_description' => 'توضیح کوتاه',
            'body' => 'متن خدمت',
            'icon' => 'آیکن',
            'image' => 'تصویر',
            'link_type' => 'نوع لینک',
            'link' => 'لینک',
            'target' => 'نحوه باز شدن لینک',
            'category_id' => 'دسته‌بندی',
            'status' => 'وضعیت',
            'published_at' => 'تاریخ انتشار',
            'rejected_reason' => 'دلیل رد',
            'sort_order' => 'ترتیب نمایش',
            'is_active' => 'فعال',
        ]);
    }

    private function serviceData(array $validated, ?ElectronicService $service = null): array
    {
        $validated = $this->sanitizeRichTextFields($validated, ['body', 'excerpt', 'short_description', 'description', 'content', 'footer_description', 'site_description']);

        $linkType = $validated['link_type'];

        return [
            'title' => $validated['title'],
            'slug' => $this->uniqueSlug($validated['slug'] ?: $validated['title'], $service),
            'short_description' => $validated['short_description'] ?? null,
            'body' => $validated['body'] ?? null,
            'icon' => $validated['icon'] ?? null,
            'link_type' => $linkType,
            'link' => $linkType === 'none' ? null : ($validated['link'] ?? null),
            'target' => $validated['target'],
            'category_id' => $validated['category_id'] ?? null,
            'status' => $validated['status'],
            'published_at' => ($validated['status'] === 'published' && empty($validated['published_at'])) ? now() : ($validated['published_at'] ?? null),
            'rejected_reason' => $validated['rejected_reason'] ?? null,
            'approved_by' => in_array($validated['status'], ['approved', 'published'], true) ? (auth()->id() ?: $service?->approved_by) : $service?->approved_by,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => (bool) $validated['is_active'],
        ];
    }

    private function uniqueSlug(string $value, ?ElectronicService $service = null): string
    {
        return app(SlugService::class)->unique(ElectronicService::class, $value, $service?->id, 'slug', strtolower(class_basename(ElectronicService::class)));

        $baseSlug = Str::slug($value) ?: Str::random(8);
        $slug = $baseSlug;
        $counter = 2;

        while (ElectronicService::query()->where('slug', $slug)->when($service, fn ($query) => $query->whereKeyNot($service->id))->exists()) {
            $slug = $baseSlug.'-'.$counter++;
        }

        return $slug;
    }

    private function categories()
    {
        return Category::query()->active()->where('type', 'service')->orderBy('sort_order')->orderBy('title')->get();
    }

    private function formData(): array
    {
        return [
            'categories' => $this->categories(),
            'statusLabels' => ElectronicService::statusLabels(),
            'linkTypeLabels' => ElectronicService::linkTypeLabels(),
            'targetLabels' => ElectronicService::targetLabels(),
        ];
    }
}
