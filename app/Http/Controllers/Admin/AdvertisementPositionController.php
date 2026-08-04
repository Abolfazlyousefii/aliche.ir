<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdvertisementPosition;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdvertisementPositionController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $status = (string) $request->query('status', '');

        $positions = AdvertisementPosition::query()
            ->withCount('advertisements')
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('title', 'like', "%{$search}%")
                ->orWhere('key', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")))
            ->when($status !== '', fn ($query) => $query->where('is_active', $status === 'active'))
            ->orderBy('title')
            ->paginate(15)
            ->withQueryString();

        return view('admin.advertisement_positions.index', compact('positions', 'search', 'status'));
    }

    public function create(): View
    {
        return view('admin.advertisement_positions.create');
    }

    public function store(Request $request): RedirectResponse
    {
        AdvertisementPosition::create($this->validatedData($request));

        return redirect()->route('admin.advertisement_positions.index')->with('success', 'جایگاه تبلیغاتی با موفقیت ایجاد شد.');
    }

    public function edit(AdvertisementPosition $advertisementPosition): View
    {
        return view('admin.advertisement_positions.edit', compact('advertisementPosition'));
    }

    public function update(Request $request, AdvertisementPosition $advertisementPosition): RedirectResponse
    {
        $advertisementPosition->update($this->validatedData($request, $advertisementPosition));

        return redirect()->route('admin.advertisement_positions.index')->with('success', 'جایگاه تبلیغاتی با موفقیت ویرایش شد.');
    }

    public function destroy(AdvertisementPosition $advertisementPosition): RedirectResponse
    {
        $advertisementPosition->delete();

        return redirect()->route('admin.advertisement_positions.index')->with('success', 'جایگاه تبلیغاتی با موفقیت حذف شد.');
    }

    private function validatedData(Request $request, ?AdvertisementPosition $position = null): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'key' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9_\-]+$/', Rule::unique('advertisement_positions', 'key')->ignore($position?->id)],
            'description' => ['nullable', 'string'],
            'width' => ['nullable', 'integer', 'min:1'],
            'height' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['required', Rule::in(['0', '1'])],
        ], [], [
            'title' => 'عنوان',
            'key' => 'کلید جایگاه',
            'description' => 'توضیحات',
            'width' => 'عرض',
            'height' => 'ارتفاع',
            'is_active' => 'فعال',
        ]);

        $validated = $this->sanitizeRichTextFields($validated, ['description']);

        $validated['key'] = $this->uniqueKey($validated['key'] ?: $validated['title'], $position);
        $validated['is_active'] = (bool) $validated['is_active'];

        return $validated;
    }

    private function uniqueKey(string $value, ?AdvertisementPosition $position = null): string
    {
        $baseKey = Str::slug($value, '_') ?: Str::random(8);
        $key = $baseKey;
        $counter = 2;

        while (AdvertisementPosition::query()
            ->where('key', $key)
            ->when($position, fn ($query) => $query->whereKeyNot($position->id))
            ->exists()) {
            $key = $baseKey.'_'.$counter++;
        }

        return $key;
    }
}
