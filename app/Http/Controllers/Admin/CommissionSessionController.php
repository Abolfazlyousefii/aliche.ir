<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\CommissionSession;
use App\Support\PublicStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CommissionSessionController extends Controller
{
    public function index(Commission $commission): View
    {
        $sessions = $commission->sessions()->paginate(15)->withQueryString();

        return view('admin.commission_sessions.index', compact('commission', 'sessions'));
    }

    public function create(Commission $commission): View
    {
        return view('admin.commission_sessions.create', ['commission' => $commission, 'statusLabels' => CommissionSession::statusLabels()]);
    }

    public function store(Request $request, Commission $commission): RedirectResponse
    {
        PublicStorage::ensureDirectories(['commission-sessions/minutes', 'commission-sessions/attachments', 'commission-sessions/images']);

        $validated = $this->sanitizeRichTextFields($this->validatedData($request), ['body', 'excerpt', 'short_description', 'description', 'content', 'footer_description', 'site_description']);
        $session = $commission->sessions()->create([
            ...$this->sessionData($validated),
            'created_by' => $request->user()?->id,
            'minutes_file' => $request->hasFile('minutes_file') ? $request->file('minutes_file')->store('commission-sessions/minutes', 'public') : null,
            'attachments' => $this->storeFiles($request, 'attachments', 'commission-sessions/attachments'),
            'images' => $this->storeFiles($request, 'images', 'commission-sessions/images'),
        ]);

        return redirect()->route('admin.commissions.sessions.show', [$commission, $session])->with('success', 'جلسه کمیسیون با موفقیت ایجاد شد.');
    }

    public function show(Commission $commission, CommissionSession $session): View
    {
        $this->ensureSessionBelongsToCommission($commission, $session);

        return view('admin.commission_sessions.show', compact('commission', 'session'));
    }

    public function edit(Commission $commission, CommissionSession $session): View
    {
        $this->ensureSessionBelongsToCommission($commission, $session);

        return view('admin.commission_sessions.edit', [
            'commission' => $commission,
            'session' => $session,
            'statusLabels' => CommissionSession::statusLabels(),
            'sessionDateJalali' => jalali_input_date($session->session_date) ?: '',
        ]);
    }

    public function update(Request $request, Commission $commission, CommissionSession $session): RedirectResponse
    {
        $this->ensureSessionBelongsToCommission($commission, $session);
        PublicStorage::ensureDirectories(['commission-sessions/minutes', 'commission-sessions/attachments', 'commission-sessions/images']);

        $validated = $this->sanitizeRichTextFields($this->validatedData($request, $session), ['body', 'excerpt', 'short_description', 'description', 'content', 'footer_description', 'site_description']);
        $data = $this->sessionData($validated);

        if ($request->hasFile('minutes_file')) {
            if ($session->minutes_file) {
                Storage::disk('public')->delete($session->minutes_file);
            }
            $data['minutes_file'] = $request->file('minutes_file')->store('commission-sessions/minutes', 'public');
        }

        $data['attachments'] = $this->syncFiles($request, $session, 'attachments', 'commission-sessions/attachments');
        $data['images'] = $this->syncFiles($request, $session, 'images', 'commission-sessions/images');
        $session->update($data);

        return redirect()->route('admin.commissions.sessions.show', [$commission, $session])->with('success', 'جلسه کمیسیون با موفقیت ویرایش شد.');
    }

    public function destroy(Commission $commission, CommissionSession $session): RedirectResponse
    {
        $this->ensureSessionBelongsToCommission($commission, $session);
        if ($session->minutes_file) {
            Storage::disk('public')->delete($session->minutes_file);
        }
        foreach (array_merge($session->attachments ?? [], $session->images ?? []) as $file) {
            Storage::disk('public')->delete($file['path'] ?? '');
        }
        $session->delete();

        return redirect()->route('admin.commissions.sessions.index', $commission)->with('success', 'جلسه کمیسیون با موفقیت حذف شد.');
    }

    private function validatedData(Request $request, ?CommissionSession $session = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'session_date_jalali' => ['nullable', 'regex:/^\d{4}[\/\-]\d{1,2}[\/\-]\d{1,2}$/'],
            'session_time' => ['nullable', 'date_format:H:i'],
            'minutes_file' => ['nullable', 'file', 'max:10240'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'max:10240'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'max:4096'],
            'existing_attachments' => ['nullable', 'array'],
            'existing_images' => ['nullable', 'array'],
            'status' => ['required', Rule::in(app(\App\Services\ContentApprovalService::class)->allowedStatusesFor($request->user(), ['commissions.approve', 'commissions.publish']))],
            'rejected_reason' => ['nullable', 'required_if:status,rejected', 'string', 'max:1000'],
            'published_at' => ['nullable', 'date'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['required', Rule::in(['0', '1'])],
        ]);
    }

    private function sessionData(array $validated): array
    {
        $validated = $this->sanitizeRichTextFields($validated, ['body', 'excerpt', 'short_description', 'description', 'content', 'footer_description', 'site_description']);

        return [
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'session_date' => filled($validated['session_date_jalali'] ?? null) ? jalali_to_gregorian_datetime(trim(($validated['session_date_jalali'] ?? '').' '.($validated['session_time'] ?? ''))) : null,
            'status' => $validated['status'],
            'published_at' => ($validated['status'] === 'published' && empty($validated['published_at'])) ? now() : ($validated['published_at'] ?? null),
            'rejected_reason' => $validated['rejected_reason'] ?? null,
            'approved_by' => in_array($validated['status'], ['approved', 'published'], true) ? (auth()->id() ?: null) : null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => (bool) $validated['is_active'],
        ];
    }

    private function storeFiles(Request $request, string $field, string $directory): array
    {
        if (! $request->hasFile($field)) {
            return [];
        }
        return collect($request->file($field))->map(fn ($file) => ['name' => $file->getClientOriginalName(), 'path' => $file->store($directory, 'public')])->values()->all();
    }

    private function syncFiles(Request $request, CommissionSession $session, string $field, string $directory): array
    {
        $existingField = 'existing_'.$field;
        $existing = collect($session->{$field} ?? [])->map(function ($file, $index) use ($request, $existingField) {
            $payload = $request->input("{$existingField}.{$index}", []);
            $file['delete'] = ($payload['delete'] ?? null) === '1';
            return $file;
        });
        $existing->where('delete', true)->each(fn ($file) => Storage::disk('public')->delete($file['path'] ?? ''));

        return $existing->reject(fn ($file) => $file['delete'])->map(fn ($file) => collect($file)->except('delete')->all())->values()
            ->merge($this->storeFiles($request, $field, $directory))->all();
    }

    private function ensureSessionBelongsToCommission(Commission $commission, CommissionSession $session): void
    {
        abort_if($session->commission_id !== $commission->id, 404);
    }

}
