<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\GuildUnion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ComplaintController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $status = (string) $request->query('status', '');
        $unionId = $request->query('union_id');

        $complaints = Complaint::query()
            ->visibleTo($request->user())
            ->with('union')
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('tracking_code', 'like', "%{$search}%")
                ->orWhere('full_name', 'like', "%{$search}%")
                ->orWhere('mobile', 'like', "%{$search}%")
                ->orWhere('subject', 'like', "%{$search}%")))
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($unionId && $request->user()->hasRole('super-admin'), fn ($query) => $query->where('union_id', $unionId))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.complaints.index', [
            'complaints' => $complaints,
            'search' => $search,
            'status' => $status,
            'unionId' => $unionId,
            'unions' => $this->availableUnions($request),
            'statusLabels' => Complaint::statusLabels(),
        ]);
    }

    public function show(Request $request, Complaint $complaint): View
    {
        $this->authorizeVisible($request, $complaint);
        $complaint->load(['union', 'answerer']);

        return view('admin.complaints.show', [
            'complaint' => $complaint,
            'statusLabels' => Complaint::statusLabels(),
        ]);
    }

    public function edit(Request $request, Complaint $complaint): View
    {
        $this->authorizeVisible($request, $complaint);
        $complaint->load(['union', 'answerer']);

        return view('admin.complaints.edit', [
            'complaint' => $complaint,
            'statusLabels' => Complaint::statusLabels(),
        ]);
    }

    public function update(Request $request, Complaint $complaint): RedirectResponse
    {
        $this->authorizeVisible($request, $complaint);

        $validated = $request->validate([
            'status' => ['required', Rule::in(Complaint::STATUSES)],
            'internal_note' => ['nullable', 'string', 'max:5000'],
        ], [], [
            'status' => 'وضعیت',
            'internal_note' => 'یادداشت داخلی',
        ]);

        $complaint->update($validated);

        return redirect()->route('admin.complaints.show', $complaint)->with('success', 'وضعیت و یادداشت داخلی شکایت به‌روزرسانی شد.');
    }

    public function reply(Request $request, Complaint $complaint): RedirectResponse
    {
        $this->authorizeVisible($request, $complaint);

        $validated = $request->validate([
            'admin_response' => ['required', 'string', 'max:5000'],
            'status' => ['required', Rule::in(Complaint::STATUSES)],
        ], [], [
            'admin_response' => 'پاسخ شکایت',
            'status' => 'وضعیت',
        ]);

        $complaint->update([
            'admin_response' => $validated['admin_response'],
            'status' => $validated['status'],
            'answered_by' => $request->user()->id,
            'answered_at' => now(),
        ]);

        return redirect()->route('admin.complaints.show', $complaint)->with('success', 'پاسخ شکایت ثبت شد.');
    }

    public function destroy(Request $request, Complaint $complaint): RedirectResponse
    {
        $this->authorizeVisible($request, $complaint);

        if ($complaint->attachment) {
            Storage::disk('public')->delete($complaint->attachment);
        }

        $complaint->delete();

        return redirect()->route('admin.complaints.index')->with('success', 'شکایت با موفقیت حذف شد.');
    }

    public function download(Request $request, Complaint $complaint): StreamedResponse
    {
        $this->authorizeVisible($request, $complaint);
        abort_unless($complaint->attachment && Storage::disk('public')->exists($complaint->attachment), 404);

        return Storage::disk('public')->download($complaint->attachment);
    }

    private function authorizeVisible(Request $request, Complaint $complaint): void
    {
        abort_unless($request->user()->hasRole('super-admin') || (int) $complaint->union_id === (int) $request->user()->union_id, 403);
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
