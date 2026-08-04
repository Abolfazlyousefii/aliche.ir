<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUnionMemberRequest;
use App\Http\Requests\Admin\UpdateUnionMemberRequest;
use App\Models\GuildUnion;
use App\Models\UnionMember;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class UnionMemberController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $status = (string) $request->query('status', '');
        $unionId = $request->query('union_id');

        $members = UnionMember::query()
            ->visibleTo($request->user())
            ->with('union')
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('full_name', 'like', "%{$search}%")
                ->orWhere('national_code', 'like', "%{$search}%")
                ->orWhere('mobile', 'like', "%{$search}%")
                ->orWhere('membership_code', 'like', "%{$search}%")
                ->orWhere('business_name', 'like', "%{$search}%")))
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($unionId && $request->user()->hasRole('super-admin'), fn ($query) => $query->where('union_id', $unionId))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.union_members.index', [
            'members' => $members,
            'search' => $search,
            'status' => $status,
            'unionId' => $unionId,
            'unions' => $this->availableUnions($request),
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.union_members.create', [
            'member' => null,
            'unions' => $this->availableUnions($request),
        ]);
    }

    public function store(StoreUnionMemberRequest $request): RedirectResponse
    {
        $data = $this->memberData($request->validated());
        $data['attachments'] = $this->storeAttachments($request);

        $member = UnionMember::create($data);

        return redirect()->route('admin.union_members.show', $member)->with('success', 'عضو اتحادیه با موفقیت ایجاد شد.');
    }

    public function show(Request $request, UnionMember $unionMember): View
    {
        $this->authorizeVisible($request, $unionMember);
        $unionMember->load('union');

        return view('admin.union_members.show', ['member' => $unionMember]);
    }

    public function edit(Request $request, UnionMember $unionMember): View
    {
        $this->authorizeVisible($request, $unionMember);
        $unionMember->load('union');

        return view('admin.union_members.edit', [
            'member' => $unionMember,
            'unions' => $this->availableUnions($request),
        ]);
    }

    public function update(UpdateUnionMemberRequest $request, UnionMember $unionMember): RedirectResponse
    {
        $this->authorizeVisible($request, $unionMember);
        $data = $this->memberData($request->validated());
        $data['attachments'] = $this->updatedAttachments($request, $unionMember);

        $unionMember->update($data);

        return redirect()->route('admin.union_members.show', $unionMember)->with('success', 'عضو اتحادیه با موفقیت ویرایش شد.');
    }

    public function destroy(Request $request, UnionMember $unionMember): RedirectResponse
    {
        $this->authorizeVisible($request, $unionMember);

        foreach ($unionMember->attachments ?? [] as $attachment) {
            Storage::disk('public')->delete($attachment['path'] ?? '');
        }

        $unionMember->delete();

        return redirect()->route('admin.union_members.index')->with('success', 'عضو اتحادیه با موفقیت حذف شد.');
    }

    /** @param array<string, mixed> $validated @return array<string, mixed> */
    private function memberData(array $validated): array
    {
        $validated = $this->sanitizeRichTextFields($validated, ['body', 'excerpt', 'short_description', 'description', 'content', 'footer_description', 'site_description']);

        return [
            'union_id' => $validated['union_id'],
            'full_name' => $validated['full_name'],
            'national_code' => $validated['national_code'] ?? null,
            'mobile' => $validated['mobile'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'membership_code' => $validated['membership_code'] ?? null,
            'business_name' => $validated['business_name'] ?? null,
            'business_license_number' => $validated['business_license_number'] ?? null,
            'address' => $validated['address'] ?? null,
            'status' => $validated['status'],
            'description' => $validated['description'] ?? null,
            'is_active' => (bool) $validated['is_active'],
        ];
    }

    /** @return array<int, array{name: string, path: string}> */
    private function storeAttachments(Request $request): array
    {
        if (! $request->hasFile('attachments')) {
            return [];
        }

        return collect($request->file('attachments'))->map(fn ($file) => [
            'name' => $file->getClientOriginalName(),
            'path' => $file->store('union-members/attachments', 'public'),
        ])->all();
    }

    /** @return array<int, array{name: string, path: string}> */
    private function updatedAttachments(Request $request, UnionMember $member): array
    {
        $attachments = collect($member->attachments ?? []);
        $requestedDeletePaths = collect($request->input('delete_attachments', []))->filter()->values();
        $deletePaths = $attachments
            ->pluck('path')
            ->filter(fn ($path) => $requestedDeletePaths->contains($path))
            ->values();

        if ($deletePaths->isNotEmpty()) {
            $deletePaths->each(fn ($path) => Storage::disk('public')->delete($path));
            $attachments = $attachments->reject(fn ($attachment) => $deletePaths->contains($attachment['path'] ?? ''));
        }

        return $attachments->merge($this->storeAttachments($request))->values()->all();
    }

    private function authorizeVisible(Request $request, UnionMember $member): void
    {
        abort_unless($request->user()->hasRole('super-admin') || (int) $member->union_id === (int) $request->user()->union_id, 403);
    }

    private function availableUnions(Request $request)
    {
        $query = GuildUnion::query()->orderBy('title')->orderBy('name');

        if (! $request->user()->hasRole('super-admin')) {
            $query->whereKey($request->user()->union_id ?: 0);
        }

        return $query->get();
    }
}
