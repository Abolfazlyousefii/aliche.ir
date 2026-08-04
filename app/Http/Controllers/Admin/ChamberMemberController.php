<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Concerns\SelectsMedia;
use App\Models\ChamberMember;
use App\Models\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ChamberMemberController extends Controller
{
    use SelectsMedia;
    public function index(): View
    {
        return view('admin.chamber_members.index', [
            'members' => ChamberMember::query()->orderBy('sort_order')->latest()->paginate(20),
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
        if (($data['is_active'] ?? false) && ChamberMember::query()->where('is_active', true)->count() >= 5) {
            return back()->withInput()->withErrors(['is_active' => 'بیشتر از پنج عضو فعال برای هیئت‌مدیره قابل ثبت نیست.']);
        }

        ChamberMember::create($data);

        return redirect()->route('admin.chamber_members.index')->with('success', 'عضو اتاق اصناف اضافه شد.');
    }

    public function edit(ChamberMember $chamberMember): View
    {
        return view('admin.chamber_members.edit', ['member' => $chamberMember, 'mediaItems' => $this->mediaItems()]);
    }

    public function update(Request $request, ChamberMember $chamberMember): RedirectResponse
    {
        $data = $this->validated($request);

        if (($data['is_active'] ?? false) && ! $chamberMember->is_active && ChamberMember::query()->where('is_active', true)->count() >= 5) {
            return back()->withInput()->withErrors(['is_active' => 'بیشتر از پنج عضو فعال برای هیئت‌مدیره قابل ثبت نیست.']);
        }

        if ($photo = $this->uploadedOrSelectedImage($request, 'photo', 'chamber-members')) {
            if ($chamberMember->photo && str_starts_with($chamberMember->photo, 'chamber-members/')) {
                Storage::disk('public')->delete($chamberMember->photo);
            }
            $data['photo'] = $photo;
        }

        $chamberMember->update($data);

        return redirect()->route('admin.chamber_members.index')->with('success', 'عضو اتاق اصناف ویرایش شد.');
    }

    public function destroy(ChamberMember $chamberMember): RedirectResponse
    {
        if ($chamberMember->photo && str_starts_with($chamberMember->photo, 'chamber-members/')) {
            Storage::disk('public')->delete($chamberMember->photo);
        }

        $chamberMember->delete();

        return redirect()->route('admin.chamber_members.index')->with('success', 'عضو اتاق اصناف حذف شد.');
    }

    private function mediaItems()
    {
        return Media::query()->images()->latest()->take(200)->get();
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'position' => ['required', 'string', 'max:150'],
            'photo' => ['nullable', 'image', 'max:4096'],
            'photo_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ]);
    }
}
