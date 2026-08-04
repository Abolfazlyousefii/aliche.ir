<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $type = (string) $request->query('type', '');

        $categories = Category::query()
            ->when($type !== '', fn ($query) => $query->where('type', $type))
            ->orderBy('type')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->paginate(20)
            ->withQueryString();

        return view('admin.categories.index', [
            'categories' => $categories,
            'types' => $this->typeLabels(),
            'type' => $type,
        ]);
    }

    public function create(): View
    {
        return view('admin.categories.create', ['category' => null, 'types' => $this->typeLabels(), 'icons' => $this->iconOptions()]);
    }

    public function store(Request $request): RedirectResponse
    {
        Category::create($this->validatedData($request));

        return redirect()->route('admin.categories.index')->with('success', 'دسته‌بندی با موفقیت ایجاد شد.');
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.edit', ['category' => $category, 'types' => $this->typeLabels(), 'icons' => $this->iconOptions()]);
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $category->update($this->validatedData($request, $category));

        return redirect()->route('admin.categories.index')->with('success', 'دسته‌بندی با موفقیت ویرایش شد.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'دسته‌بندی حذف شد.');
    }

    private function validatedData(Request $request, ?Category $category = null): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'slug' => ['nullable', 'string', 'max:190', Rule::unique('categories', 'slug')->ignore($category?->id)],
            'type' => ['required', Rule::in(array_keys($this->typeLabels()))],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:50'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ], [], $this->validationAttributes());

        $validated['slug'] = app(\App\Services\SlugService::class)->unique(Category::class, ($validated['slug'] ?? '') ?: $validated['title'].'-'.$validated['type'], $category?->id, 'slug', 'category');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        return $validated;
    }

    /** @return array<int, string> */
    private function iconOptions(): array
    {
        return ['🏷️', '📰', '📢', '🖼️', '🎬', '⚡', '💻', '🏢', '🛒', '🧰', '🎯', '📄', '📚', '☎️', '✅'];
    }

    /** @return array<string, string> */
    private function validationAttributes(): array
    {
        return [
            'title' => 'عنوان',
            'slug' => 'نامک',
            'type' => 'نوع دسته‌بندی',
            'description' => 'توضیحات',
            'icon' => 'آیکون',
            'sort_order' => 'ترتیب نمایش',
            'is_active' => 'وضعیت',
        ];
    }

    private function typeLabels(): array
    {
        return [
            'news' => 'اخبار',
            'tourism' => 'گردشگری',
            'gallery' => 'گالری',
            'video' => 'ویدیو',
            'service' => 'خدمات',
            'system' => 'سامانه‌ها',
            'union' => 'اتحادیه‌ها',
        ];
    }
}
