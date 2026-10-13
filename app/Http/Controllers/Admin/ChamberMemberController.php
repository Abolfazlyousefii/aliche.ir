<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\SelectsMedia;
use App\Http\Controllers\Controller;
use App\Models\ChamberMember;
use App\Models\Media;
use App\Models\Page;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use App\Rules\SafeImageUpload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChamberMemberController extends Controller
{
    use SelectsMedia;

    public function board(): RedirectResponse
    {
        $page = Page::chamberAboutPage();

        return $page
            ? redirect()->to(route('admin.pages.edit', $page).'#chamber-board')
            : redirect()->route('admin.pages.index')->with('error', 'ابتدا صفحهٔ درباره اتاق را با قالب «درباره اتاق و هیئت‌رئیسه» تعریف کنید.');
    }

    private function memberList(): RedirectResponse
    {
        $page = Page::chamberAboutPage();

        return $page && request()->user()?->hasPermission('pages.edit')
            ? redirect()->to(route('admin.pages.edit', $page).'#chamber-board')
            : redirect()->route('admin.chamber_members.index');
    }

    public function index(): View
    {
        return view('admin.chamber_members.index', [
            'members' => ChamberMember::query()->orderBy('sort_order')->latest()->paginate(20)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.chamber_members.create', ['member' => new ChamberMember(['is_active' => true]), 'mediaItems' => $this->mediaItems()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['photo'] = $this->uploadedOrSelectedImage($request, 'photo', 'chamber-members');
        $this->saveMember(new ChamberMember, $data);

        return $this->memberList()->with('success', 'عضو اتاق اصناف اضافه شد.');
    }

    public function edit(ChamberMember $chamberMember): View
    {
        return view('admin.chamber_members.edit', ['member' => $chamberMember, 'mediaItems' => $this->mediaItems()]);
    }

    public function update(Request $request, ChamberMember $chamberMember): RedirectResponse
    {
        $data = $this->validated($request, $chamberMember);

        if ($photo = $this->uploadedOrSelectedImage($request, 'photo', 'chamber-members')) {
            $data['photo'] = $photo;
        }

        $this->saveMember($chamberMember, $data);

        return $this->memberList()->with('success', 'عضو اتاق اصناف ویرایش شد.');
    }

    public function destroy(ChamberMember $chamberMember): RedirectResponse
    {
        $chamberMember->delete();

        return $this->memberList()->with('success', 'عضو اتاق اصناف حذف شد.');
    }

    private function mediaItems()
    {
        return Media::query()->images()->latest()->take(200)->get();
    }

    private function saveMember(ChamberMember $member, array $data): void
    {
        try {
            $member->fill($data)->save();
        } catch (QueryException $exception) {
            if (str_contains($exception->getMessage(), 'active_president_slot')) {
                throw ValidationException::withMessages(['is_president' => 'یک رئیس فعال از قبل ثبت شده است. ابتدا رئیس قبلی را غیرفعال کنید.']);
            }
            throw $exception;
        }
    }

    private function validated(Request $request, ?ChamberMember $member = null): array
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'position' => ['required', 'string', 'max:150'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'photo' => ['nullable', 'bail', 'file', new SafeImageUpload, 'max:'.config('media.max_upload_kilobytes', 5120)],
            'photo_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
            'is_president' => ['sometimes', 'boolean'],
        ]);
        $data['is_president'] = (bool) ($data['is_president'] ?? $member?->is_president ?? false);
        if ($data['is_active'] && $data['is_president'] && ChamberMember::query()
            ->where('is_active', true)->where('is_president', true)
            ->when($member?->exists, fn ($query) => $query->whereKeyNot($member->id))->exists()) {
            throw ValidationException::withMessages(['is_president' => 'یک رئیس فعال از قبل ثبت شده است. ابتدا رئیس قبلی را غیرفعال کنید.']);
        }

        return $data;
    }
}
