<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\SelectsMedia;
use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Models\AdvertisementPosition;
use App\Rules\SafeImageUpload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdvertisementController extends Controller
{
    use SelectsMedia;

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $positionId = $request->query('position_id');
        $status = (string) $request->query('status', '');

        $advertisements = Advertisement::query()
            ->with('position')
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('title', 'like', "%{$search}%")
                ->orWhere('link', 'like', "%{$search}%")))
            ->when($positionId, fn ($query) => $query->where('position_id', $positionId))
            ->when($status === 'active', fn ($query) => $query->where('is_active', true))
            ->when($status === 'inactive', fn ($query) => $query->where('is_active', false))
            ->when($status === 'displayable', fn ($query) => $query->displayable())
            ->when($status === 'scheduled', fn ($query) => $query->where('is_active', true)->where('starts_at', '>', now()))
            ->when($status === 'expired', fn ($query) => $query->whereNotNull('expires_at')->where('expires_at', '<', now()))
            ->orderBy('sort_order')
            ->latest('starts_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.advertisements.index', [
            'advertisements' => $advertisements,
            'positions' => $this->positions(),
            'search' => $search,
            'positionId' => $positionId,
            'status' => $status,
        ]);
    }

    public function create(): View
    {
        return view('admin.advertisements.create', [
            'positions' => $this->positions(),
            'targetLabels' => Advertisement::targetLabels(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedData($request);

        Advertisement::create([
            ...$this->advertisementData($validated),
            'image' => $this->uploadedOrSelectedImage($request, 'image', 'advertisements'),
        ]);

        return redirect()->route('admin.advertisements.index')->with('success', 'تبلیغ با موفقیت ایجاد شد.');
    }

    public function show(Advertisement $advertisement): View
    {
        $advertisement->load('position');

        return view('admin.advertisements.show', compact('advertisement'));
    }

    public function edit(Advertisement $advertisement): View
    {
        return view('admin.advertisements.edit', [
            'advertisement' => $advertisement,
            'positions' => $this->positions(),
            'targetLabels' => Advertisement::targetLabels(),
        ]);
    }

    public function update(Request $request, Advertisement $advertisement): RedirectResponse
    {
        $validated = $this->validatedData($request, $advertisement);
        $data = $this->advertisementData($validated);

        if ($image = $this->uploadedOrSelectedImage($request, 'image', 'advertisements')) {
            Storage::disk('public')->delete($advertisement->image);
            $data['image'] = $image;
        }

        $advertisement->update($data);

        return redirect()->route('admin.advertisements.show', $advertisement)->with('success', 'تبلیغ با موفقیت ویرایش شد.');
    }

    public function destroy(Advertisement $advertisement): RedirectResponse
    {
        Storage::disk('public')->delete($advertisement->image);
        $advertisement->delete();

        return redirect()->route('admin.advertisements.index')->with('success', 'تبلیغ با موفقیت حذف شد.');
    }

    private function validatedData(Request $request, ?Advertisement $advertisement = null): array
    {
        return $request->validate([
            'position_id' => ['required', 'exists:advertisement_positions,id'],
            'title' => ['required', 'string', 'max:255'],
            'image' => [$advertisement ? 'nullable' : 'required_without:image_media_id', 'bail', 'file', new SafeImageUpload, 'max:'.config('media.max_upload_kilobytes', 5120)],
            'image_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'link' => ['nullable', 'url', 'max:500'],
            'target' => ['required', Rule::in(Advertisement::TARGETS)],
            'starts_at' => ['required', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'clicks_count' => ['nullable', 'integer', 'min:0'],
            'views_count' => ['nullable', 'integer', 'min:0'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['required', Rule::in(['0', '1'])],
        ], [], [
            'position_id' => 'جایگاه',
            'title' => 'عنوان',
            'image' => 'تصویر',
            'link' => 'لینک',
            'target' => 'نحوه باز شدن لینک',
            'starts_at' => 'زمان شروع',
            'expires_at' => 'زمان پایان',
            'clicks_count' => 'تعداد کلیک',
            'views_count' => 'تعداد نمایش',
            'sort_order' => 'ترتیب نمایش',
            'is_active' => 'فعال',
        ]);
    }

    private function advertisementData(array $validated): array
    {
        return [
            'position_id' => $validated['position_id'],
            'title' => $validated['title'],
            'link' => $validated['link'] ?? null,
            'target' => $validated['target'],
            'starts_at' => $validated['starts_at'],
            'expires_at' => $validated['expires_at'] ?? null,
            'clicks_count' => $validated['clicks_count'] ?? 0,
            'views_count' => $validated['views_count'] ?? 0,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => (bool) $validated['is_active'],
        ];
    }

    private function positions()
    {
        return AdvertisementPosition::query()->orderBy('title')->get();
    }
}
