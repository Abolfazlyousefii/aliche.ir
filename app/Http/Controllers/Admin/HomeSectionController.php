<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class HomeSectionController extends Controller
{
    public function index(): View
    {
        $sections = HomeSection::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('admin.home_sections.index', compact('sections'));
    }

    public function edit(HomeSection $homeSection): View
    {
        return view('admin.home_sections.edit', [
            'section' => $homeSection,
            'settingsJson' => json_encode($homeSection->settings ?? new \stdClass(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }

    public function update(Request $request, HomeSection $homeSection): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:500'],
            'content' => ['nullable', 'string'],
            'settings' => ['nullable', 'json'],
            'is_active' => ['required', Rule::in(['0', '1'])],
            'sort_order' => ['required', 'integer', 'min:0'],
        ], [], [
            'title' => 'عنوان',
            'subtitle' => 'توضیح کوتاه',
            'content' => 'محتوا',
            'settings' => 'تنظیمات اختصاصی',
            'is_active' => 'وضعیت',
            'sort_order' => 'ترتیب نمایش',
        ]);

        $validated = $this->sanitizeRichTextFields($validated, ['content']);

        $homeSection->update([
            'title' => $validated['title'],
            'subtitle' => $validated['subtitle'] ?? null,
            'content' => $validated['content'] ?? null,
            'settings' => filled($validated['settings'] ?? null) ? json_decode($validated['settings'], true) : null,
            'is_active' => (bool) $validated['is_active'],
            'sort_order' => $validated['sort_order'],
        ]);

        return redirect()->route('admin.home_sections.index')->with('success', 'سکشن صفحه اصلی با موفقیت ویرایش شد.');
    }

    public function sort(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sections' => ['required', 'array'],
            'sections.*' => ['integer', 'exists:home_sections,id'],
        ], [], [
            'sections' => 'ترتیب سکشن‌ها',
        ]);

        foreach ($validated['sections'] as $index => $sectionId) {
            HomeSection::query()->whereKey($sectionId)->update(['sort_order' => ($index + 1) * 10]);
        }

        return redirect()->route('admin.home_sections.index')->with('success', 'ترتیب سکشن‌های صفحه اصلی ذخیره شد.');
    }
}
