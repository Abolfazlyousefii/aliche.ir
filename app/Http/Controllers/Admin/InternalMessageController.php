<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GuildUnion;
use App\Models\InternalMessage;
use App\Models\Role;
use App\Models\User;
use App\Services\RichTextSanitizer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InternalMessageController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $messages = $this->baseMessageQuery($request)
            ->when(! $user?->hasPermission('messages.manage'), fn (Builder $query) => $query->where('recipient_id', $user?->id))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.messages.index', compact('messages'));
    }

    public function inbox(Request $request): View
    {
        $messages = $this->baseMessageQuery($request)
            ->where('recipient_id', $request->user()?->id)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.messages.inbox', compact('messages'));
    }

    public function sent(Request $request): View
    {
        $messages = $this->baseMessageQuery($request)
            ->where('sender_id', $request->user()?->id)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.messages.sent', compact('messages'));
    }

    public function create(): View
    {
        return view('admin.messages.create', $this->formData());
    }

    public function store(Request $request, RichTextSanitizer $sanitizer): RedirectResponse
    {
        $validated = $request->validate($this->storeRules(), [], $this->validationAttributes());
        $body = $sanitizer->sanitize($validated['body']) ?: strip_tags($validated['body']);
        $recipientIds = $this->resolveRecipientIds($validated);

        if ($recipientIds->isEmpty()) {
            return back()->withInput()->withErrors(['recipient_id' => 'هیچ گیرنده فعالی برای ارسال پیام پیدا نشد.']);
        }

        DB::transaction(function () use ($validated, $body, $recipientIds, $request) {
            $sentAt = now();
            $messageType = $this->messageTypeForSendType($validated['send_type']);
            $meta = $this->messageMeta($validated, $recipientIds->count());

            $recipientIds->each(function (int $recipientId) use ($validated, $body, $request, $sentAt, $messageType, $meta) {
                InternalMessage::create([
                    'sender_id' => $request->user()?->id,
                    'recipient_id' => $recipientId,
                    'subject' => $validated['subject'],
                    'body' => $body,
                    'type' => $messageType,
                    'priority' => $validated['priority'],
                    'allow_reply' => $request->boolean('allow_reply'),
                    'sent_at' => $sentAt,
                    'meta' => $meta,
                ]);
            });
        });

        return redirect()->route('admin.messages.sent')->with('success', 'پیام داخلی با موفقیت ارسال شد.');
    }

    public function show(Request $request, InternalMessage $message): View
    {
        $this->authorizeMessageAccess($message);

        if ($message->recipient_id === $request->user()?->id) {
            $message->markAsRead();
        }

        $message->load(['sender', 'recipient', 'parent.sender', 'parent.recipient', 'replies.sender', 'replies.recipient']);

        return view('admin.messages.show', compact('message'));
    }

    public function reply(InternalMessage $message): RedirectResponse
    {
        $this->authorizeMessageAccess($message);

        return redirect()->route('admin.messages.show', $message);
    }

    public function storeReply(Request $request, InternalMessage $message, RichTextSanitizer $sanitizer): RedirectResponse
    {
        $this->authorizeMessageAccess($message);

        abort_unless($message->allow_reply, 403);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:20000'],
        ], [], [
            'body' => 'متن پاسخ',
        ]);

        $recipientId = $message->sender_id === $request->user()?->id ? $message->recipient_id : $message->sender_id;
        abort_unless($recipientId, 422, 'گیرنده پاسخ مشخص نیست.');

        InternalMessage::create([
            'sender_id' => $request->user()?->id,
            'recipient_id' => $recipientId,
            'subject' => 'پاسخ: '.$message->subject,
            'body' => $sanitizer->sanitize($validated['body']) ?: strip_tags($validated['body']),
            'type' => InternalMessage::TYPE_REPLY,
            'priority' => $message->priority,
            'allow_reply' => true,
            'sent_at' => now(),
            'parent_id' => $message->id,
            'meta' => ['source' => 'internal_reply'],
        ]);

        return redirect()->route('admin.messages.show', $message)->with('success', 'پاسخ شما با موفقیت ثبت شد.');
    }

    public function destroy(Request $request, InternalMessage $message): RedirectResponse
    {
        $user = $request->user();
        abort_unless(
            $user?->hasPermission('messages.manage') || $message->sender_id === $user?->id || $message->recipient_id === $user?->id,
            403
        );

        $message->delete();

        return back()->with('success', 'پیام با موفقیت حذف شد.');
    }

    private function authorizeMessageAccess(InternalMessage $message): void
    {
        $user = auth()->user();

        if (
            ! $user ||
            ($message->sender_id !== $user->id &&
            $message->recipient_id !== $user->id &&
            ! $user->hasPermission('messages.manage'))
        ) {
            abort(403);
        }
    }

    private function baseMessageQuery(Request $request): Builder
    {
        $search = trim((string) $request->query('search'));
        $priority = (string) $request->query('priority', '');
        $status = (string) $request->query('status', '');

        return InternalMessage::query()
            ->with(['sender', 'recipient'])
            ->when($search !== '', fn (Builder $query) => $query->where(fn (Builder $query) => $query
                ->where('subject', 'like', "%{$search}%")
                ->orWhere('body', 'like', "%{$search}%")))
            ->when(in_array($priority, InternalMessage::PRIORITIES, true), fn (Builder $query) => $query->where('priority', $priority))
            ->when($status === 'unread', fn (Builder $query) => $query->whereNull('read_at'))
            ->when($status === 'read', fn (Builder $query) => $query->whereNotNull('read_at'));
    }

    /** @return array<string, mixed> */
    private function formData(): array
    {
        return [
            'users' => User::query()->where('is_active', true)->where('id', '!=', auth()->id())->orderBy('name')->get(['id', 'name', 'email', 'mobile', 'union_id']),
            'roles' => Role::query()->orderBy('label')->orderBy('name')->get(['id', 'name', 'label']),
            'unions' => GuildUnion::query()->where('is_active', true)->orderBy('title')->orderBy('name')->get(['id', 'name', 'title']),
            'priorities' => InternalMessage::PRIORITIES,
        ];
    }

    /** @return array<string, mixed> */
    private function storeRules(): array
    {
        return [
            'send_type' => ['required', Rule::in(['direct', 'selected', 'role', 'union', 'broadcast'])],
            'recipient_id' => ['nullable', 'required_if:send_type,direct', 'integer', 'exists:users,id'],
            'recipient_ids' => ['nullable', 'required_if:send_type,selected', 'array'],
            'recipient_ids.*' => ['integer', 'distinct', 'exists:users,id'],
            'role_id' => ['nullable', 'required_if:send_type,role', 'integer', 'exists:roles,id'],
            'union_id' => ['nullable', 'required_if:send_type,union', 'integer', 'exists:unions,id'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:20000'],
            'priority' => ['required', Rule::in(InternalMessage::PRIORITIES)],
            'allow_reply' => ['nullable', 'boolean'],
        ];
    }

    /** @param array<string, mixed> $validated */
    private function resolveRecipientIds(array $validated): \Illuminate\Support\Collection
    {
        $activeUsers = User::query()->where('is_active', true)->where('id', '!=', auth()->id());

        $recipientIds = match ($validated['send_type']) {
            'direct' => $activeUsers->whereKey($validated['recipient_id'])->pluck('id'),
            'selected' => $activeUsers->whereIn('id', $validated['recipient_ids'] ?? [])->pluck('id'),
            'role' => $activeUsers->whereHas('roles', fn (Builder $query) => $query->whereKey($validated['role_id']))->pluck('id'),
            'union' => $activeUsers->where('union_id', $validated['union_id'])->pluck('id'),
            'broadcast' => $activeUsers->pluck('id'),
        };

        return $recipientIds->unique()->values();
    }

    private function messageTypeForSendType(string $sendType): string
    {
        return match ($sendType) {
            'role' => InternalMessage::TYPE_ROLE,
            'union' => InternalMessage::TYPE_UNION,
            'broadcast' => InternalMessage::TYPE_BROADCAST,
            default => InternalMessage::TYPE_DIRECT,
        };
    }

    /** @param array<string, mixed> $validated @return array<string, mixed> */
    private function messageMeta(array $validated, int $recipientCount): array
    {
        return array_filter([
            'send_type' => $validated['send_type'],
            'role_id' => $validated['role_id'] ?? null,
            'union_id' => $validated['union_id'] ?? null,
            'recipient_count' => $recipientCount,
        ], fn ($value) => $value !== null && $value !== '');
    }

    /** @return array<string, string> */
    private function validationAttributes(): array
    {
        return [
            'send_type' => 'نوع ارسال',
            'recipient_id' => 'گیرنده',
            'recipient_ids' => 'گیرندگان',
            'role_id' => 'نقش',
            'union_id' => 'اتحادیه',
            'subject' => 'عنوان پیام',
            'body' => 'متن پیام',
            'priority' => 'اولویت',
            'allow_reply' => 'اجازه پاسخ',
        ];
    }
}
