<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GuildUnion;
use App\Models\SmsLog;
use App\Models\UnionMember;
use App\Services\Sms\SmsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SmsController extends Controller
{
    public function index(Request $request): View
    {
        $logsQuery = SmsLog::query()->visibleTo($request->user());

        return view('admin.sms.index', [
            'totalLogs' => (clone $logsQuery)->count(),
            'sentLogs' => (clone $logsQuery)->where('status', 'sent')->count(),
            'pendingLogs' => (clone $logsQuery)->where('status', 'pending')->count(),
            'failedLogs' => (clone $logsQuery)->whereIn('status', ['failed', 'partial'])->count(),
            'recentLogs' => $logsQuery->with(['sender', 'union'])->latest()->take(8)->get(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.sms.create', [
            'unions' => $this->availableUnions($request),
            'members' => $this->availableMembers($request)->with('union')->orderBy('full_name')->get(),
            'sendTypeLabels' => SmsLog::sendTypeLabels(),
            'isSuperAdmin' => $request->user()->hasRole('super-admin'),
            'userUnionId' => $request->user()->union_id,
        ]);
    }

    public function store(Request $request, SmsService $smsService): RedirectResponse
    {
        $user = $request->user();
        $allowedSendTypes = $user->hasRole('super-admin')
            ? SmsLog::SEND_TYPES
            : ['single', 'selected', 'union_all'];

        $validated = $request->validate([
            'send_type' => ['required', Rule::in($allowedSendTypes)],
            'union_id' => ['nullable', 'integer', Rule::exists('unions', 'id')],
            'single_member_id' => ['nullable', 'integer', Rule::exists('union_members', 'id')],
            'member_ids' => ['nullable', 'array'],
            'member_ids.*' => ['integer', Rule::exists('union_members', 'id')],
            'message' => ['required', 'string', 'max:1000'],
        ], [], [
            'send_type' => 'نوع ارسال',
            'union_id' => 'اتحادیه',
            'single_member_id' => 'عضو',
            'member_ids' => 'اعضای انتخاب‌شده',
            'message' => 'متن پیامک',
        ]);

        if ($validated['send_type'] === 'union_all' && ! $this->requestedUnionId($request, $validated['union_id'] ?? null)) {
            return back()->withInput()->withErrors(['union_id' => 'برای ارسال به همه اعضای اتحادیه، انتخاب اتحادیه الزامی است.']);
        }

        if ($validated['send_type'] === 'single' && empty($validated['single_member_id'])) {
            return back()->withInput()->withErrors(['single_member_id' => 'برای ارسال تکی، انتخاب عضو الزامی است.']);
        }

        if ($validated['send_type'] === 'selected' && empty($validated['member_ids'])) {
            return back()->withInput()->withErrors(['member_ids' => 'برای ارسال به اعضای انتخاب‌شده، حداقل یک عضو را انتخاب کنید.']);
        }

        $recipients = $this->resolveRecipients($request, $validated);

        if ($recipients->isEmpty()) {
            return back()->withInput()->withErrors(['member_ids' => 'هیچ گیرنده معتبری با شماره موبایل پیدا نشد.']);
        }

        $unionId = $this->resolveLogUnionId($request, $validated['send_type'], $validated['union_id'] ?? null, $recipients);
        $providerResponse = $smsService->send(
            $validated['message'],
            $recipients->pluck('mobile'),
            [
                'sender_id' => $user->id,
                'union_id' => $unionId,
                'send_type' => $validated['send_type'],
            ]
        );

        $smsLog = SmsLog::create([
            'sender_id' => $user->id,
            'union_id' => $unionId,
            'message' => $validated['message'],
            'recipients' => $recipients->map(fn (UnionMember $member) => [
                'id' => $member->id,
                'union_id' => $member->union_id,
                'union_title' => $member->union?->display_title,
                'full_name' => $member->full_name,
                'mobile' => $member->mobile,
                'membership_code' => $member->membership_code,
            ])->values()->all(),
            'recipient_count' => $recipients->count(),
            'send_type' => $validated['send_type'],
            'status' => $providerResponse['status'] ?? 'pending',
            'provider_response' => $providerResponse,
            'sent_at' => now(),
        ]);

        return redirect()->route('admin.sms.show', $smsLog)->with('success', 'پیامک با پاسخ شبیه‌سازی‌شده ثبت شد.');
    }

    public function logs(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $status = (string) $request->query('status', '');
        $sendType = (string) $request->query('send_type', '');
        $unionId = $request->query('union_id');

        $logs = SmsLog::query()
            ->visibleTo($request->user())
            ->with(['sender', 'union'])
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('message', 'like', "%{$search}%")
                ->orWhere('recipients', 'like', "%{$search}%")))
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($sendType !== '', fn ($query) => $query->where('send_type', $sendType))
            ->when($unionId && $request->user()->hasRole('super-admin'), fn ($query) => $query->where('union_id', $unionId))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.sms.logs', [
            'logs' => $logs,
            'search' => $search,
            'status' => $status,
            'sendType' => $sendType,
            'unionId' => $unionId,
            'unions' => $this->availableUnions($request),
            'sendTypeLabels' => SmsLog::sendTypeLabels(),
            'statusLabels' => SmsLog::statusLabels(),
        ]);
    }

    public function show(Request $request, SmsLog $smsLog): View
    {
        $this->authorizeVisible($request, $smsLog);
        $smsLog->load(['sender', 'union']);

        return view('admin.sms.show', compact('smsLog'));
    }

    private function resolveRecipients(Request $request, array $validated)
    {
        $query = $this->availableMembers($request)->with('union')->orderBy('full_name');

        return match ($validated['send_type']) {
            'all' => $query->get(),
            'union_all' => $query->where('union_id', $this->requestedUnionId($request, $validated['union_id'] ?? null))->get(),
            'single' => $query->whereKey($validated['single_member_id'] ?? 0)->get(),
            'selected' => $query->whereKey($validated['member_ids'] ?? [])->get(),
            default => collect(),
        };
    }

    private function requestedUnionId(Request $request, mixed $requestedUnionId): int
    {
        if (! $request->user()->hasRole('super-admin')) {
            return (int) $request->user()->union_id;
        }

        return (int) $requestedUnionId;
    }

    private function resolveLogUnionId(Request $request, string $sendType, mixed $requestedUnionId, $recipients): ?int
    {
        if (! $request->user()->hasRole('super-admin')) {
            return $request->user()->union_id;
        }

        if ($sendType === 'union_all') {
            return $requestedUnionId ? (int) $requestedUnionId : null;
        }

        $unionIds = $recipients->pluck('union_id')->unique()->values();

        return $unionIds->count() === 1 ? (int) $unionIds->first() : null;
    }

    private function availableMembers(Request $request)
    {
        $query = UnionMember::query()
            ->visibleTo($request->user())
            ->where('is_active', true)
            ->whereNotNull('mobile')
            ->where('mobile', '!=', '');

        return $query;
    }

    private function availableUnions(Request $request)
    {
        $query = GuildUnion::query()->orderBy('title')->orderBy('name');

        if (! $request->user()->hasRole('super-admin')) {
            $query->whereKey($request->user()->union_id ?: 0);
        }

        return $query->get();
    }

    private function authorizeVisible(Request $request, SmsLog $smsLog): void
    {
        abort_unless(
            $request->user()->hasRole('super-admin')
            || (int) $smsLog->sender_id === (int) $request->user()->id
            || (int) $smsLog->union_id === (int) $request->user()->union_id,
            403
        );
    }
}
