<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Support\AdminCategoryAccess;
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
        $search = trim((string) $request->query('search', ''));
        $allowed = AdminCategoryAccess::allowedTypes($request->user(), 'view');
        abort_if($allowed === [] || ($type !== '' && ! in_array($type, $allowed, true)), 403);

        $categories = Category::query()
            ->whereIn('type', $allowed)
            ->when($type !== '', fn ($query) => $query->where('type', $type))
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('title', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%")))
            ->orderBy('type')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->paginate(20)
            ->withQueryString();

        return view('admin.categories.index', [
            'categories' => $categories,
            'search' => $search,
            'types' => array_intersect_key($this->typeLabels(), array_flip($allowed)),
            'type' => $type,
            'createTypes' => AdminCategoryAccess::allowedTypes($request->user(), 'create'),
        ]);
    }

    public function create(Request $request): View
    {
        $allowed = AdminCategoryAccess::allowedTypes($request->user(), 'create');
        $type = (string) $request->query('type', '');
        abort_if($allowed === [] || ($type !== '' && ! in_array($type, $allowed, true)), 403);

        return view('admin.categories.create', [
            'category' => null,
            'types' => array_intersect_key($this->typeLabels(), array_flip($allowed)),
            'icons' => $this->iconOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(AdminCategoryAccess::can($request->user(), (string) $request->input('type'), 'create'), 403);

        Category::create($this->validatedData($request));

        return redirect()->route('admin.categories.index')->with('success', 'دسته‌بندی با موفقیت ایجاد شد.');
    }

    public function edit(Request $request, Category $category): View
    {
        abort_unless(AdminCategoryAccess::can($request->user(), $category->type, 'edit'), 403);
        $allowed = AdminCategoryAccess::allowedTypes($request->user(), 'edit');

        return view('admin.categories.edit', [
            'category' => $category,
            'types' => array_intersect_key($this->typeLabels(), array_flip($allowed)),
            'icons' => $this->iconOptions(),
        ]);
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        abort_unless(AdminCategoryAccess::can($request->user(), $category->type, 'edit')
            && AdminCategoryAccess::can($request->user(), (string) $request->input('type'), 'edit'), 403);

        $category->update($this->validatedData($request, $category));

        return redirect()->route('admin.categories.index')->with('success', 'دسته‌بندی با موفقیت ویرایش شد.');
    }

    public function destroy(Request $request, Category $category): RedirectResponse
    {
        abort_unless(AdminCategoryAccess::can($request->user(), $category->type, 'delete'), 403);

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
