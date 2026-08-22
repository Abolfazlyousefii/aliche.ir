<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\SelectsMedia;
use App\Http\Controllers\Controller;
use App\Models\UnionType;
use App\Rules\SafeImageUpload;
use App\Services\SlugService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UnionTypeController extends Controller
{
    use SelectsMedia;

    public function index(): View
    {
        $unionTypes = UnionType::query()->orderBy('sort_order')->orderBy('title')->paginate(20);

        return view('admin.union_types.index', compact('unionTypes'));
    }

    public function create(): View
    {
        return view('admin.union_types.create', ['unionType' => null, 'iconOptions' => UnionType::iconOptions()]);
    }

    public function store(Request $request): RedirectResponse
    {
        UnionType::create($this->validatedData($request) + [
            'image' => $this->storeImage($request, 'image', 'union-types/images'),
        ]);

        return redirect()->route('admin.union-types.index')->with('success', 'نوع اتحادیه با موفقیت ایجاد شد.');
    }

    public function edit(UnionType $unionType): View
    {
        return view('admin.union_types.edit', ['unionType' => $unionType, 'iconOptions' => UnionType::iconOptions()]);
    }

    public function update(Request $request, UnionType $unionType): RedirectResponse
    {
        $data = $this->validatedData($request, $unionType);

        if ($image = $this->storeImage($request, 'image', 'union-types/images')) {
            if ($unionType->image) {
                Storage::disk('public')->delete($unionType->image);
            }

            $data['image'] = $image;
        }

        $unionType->update($data);

        return redirect()->route('admin.union-types.index')->with('success', 'نوع اتحادیه با موفقیت ویرایش شد.');
    }

    public function destroy(UnionType $unionType): RedirectResponse
    {
        if ($unionType->image) {
            Storage::disk('public')->delete($unionType->image);
        }

        $unionType->delete();

        return redirect()->route('admin.union-types.index')->with('success', 'نوع اتحادیه حذف شد.');
    }

    private function validatedData(Request $request, ?UnionType $unionType = null): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'slug' => ['nullable', 'string', 'max:190', Rule::unique('union_types', 'slug')->ignore($unionType?->id)],
            'icon' => ['nullable', 'string', Rule::in(array_keys(UnionType::iconOptions()))],
            'image' => ['nullable', 'bail', 'file', new SafeImageUpload, 'max:'.config('media.max_upload_kilobytes', 5120)],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ]);

        $validated['slug'] = app(SlugService::class)->unique(UnionType::class, ($validated['slug'] ?? '') ?: $validated['title'], $unionType?->id, 'slug', 'union-type');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        return $validated;
    }

    private function storeImage(Request $request, string $field, string $directory): ?string
    {
        return $this->uploadedOrSelectedImage($request, $field, $directory);
    }
}
