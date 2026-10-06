<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Complaint;
use App\Models\ContactMessage;
use App\Models\GuildUnion;
use App\Models\SmsLog;
use App\Models\UnionMember;
use App\Services\ContentApprovalService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(ContentApprovalService $approvalService): View
    {
        $user = request()->user();
        $can = fn (string $permission): bool => $user?->hasPermission($permission) === true;

        // A dashboard is also a view of protected operational information.
        // Never display counts or details belonging to modules the user cannot view.
        $canReview = $can('pending_approvals.view');
        $canComplaints = $can('complaints.view');
        $canContact = $can('contact_messages.view');
        $canUnions = $can('unions.view');
        $canMembers = $can('union_members.view');
        $canSms = $can('sms.view');

        $pendingApprovals = $canReview
            ? $approvalService->pendingItems(null, $user)
            : collect();
        $openComplaintsCount = $canComplaints
            ? Complaint::query()->visibleTo($user)
                ->whereIn('status', ['registered', 'reviewing', 'need_more_info'])->count()
            : 0;
        $unreadContactMessagesCount = $canContact
            ? ContactMessage::query()->unread()->count()
            : 0;
        $smsQuery = $canSms ? SmsLog::query()->visibleTo($user) : null;
        $latestSmsLog = $smsQuery ? (clone $smsQuery)->latest()->first() : null;

        $stats = array_values(array_filter([
            $canReview ? ['title' => 'در انتظار بررسی', 'count' => $pendingApprovals->count(), 'icon' => 'check', 'tone' => 'warning', 'route' => 'admin.pending_approvals.index', 'hint' => 'محتوای نیازمند تصمیم'] : null,
            $canComplaints ? ['title' => 'شکایت‌های باز', 'count' => $openComplaintsCount, 'icon' => 'complaint', 'tone' => 'danger', 'route' => 'admin.complaints.index', 'hint' => 'پرونده‌های نیازمند پیگیری'] : null,
            $canContact ? ['title' => 'پیام‌های تماس جدید', 'count' => $unreadContactMessagesCount, 'icon' => 'phone', 'tone' => 'info', 'route' => 'admin.contact_messages.index', 'hint' => 'پیام‌های خوانده‌نشده'] : null,
            $canUnions ? ['title' => 'اتحادیه‌های فعال', 'count' => GuildUnion::query()->where('is_active', true)->count(), 'icon' => 'building', 'tone' => 'primary', 'route' => 'admin.unions.index', 'hint' => 'مدیریت پروفایل اتحادیه‌ها'] : null,
            $canMembers ? ['title' => 'اعضای فعال', 'count' => UnionMember::query()->visibleTo($user)->where('is_active', true)->count(), 'icon' => 'users', 'tone' => 'success', 'route' => 'admin.union_members.index', 'hint' => 'فهرست اعضای ثبت‌شده'] : null,
            $canSms ? ['title' => 'گیرندگان پیامک موفق', 'count' => (clone $smsQuery)->where('status', 'sent')->sum('recipient_count'), 'icon' => 'sms', 'tone' => 'purple', 'route' => 'admin.sms.index', 'hint' => 'بر پایه گزارش ارسال'] : null,
        ]));

        $tasks = array_values(array_filter([
            $canReview && $pendingApprovals->isNotEmpty() ? ['title' => 'بررسی محتواهای در انتظار', 'count' => $pendingApprovals->count(), 'route' => 'admin.pending_approvals.index', 'icon' => 'check'] : null,
            $canComplaints && $openComplaintsCount > 0 ? ['title' => 'پیگیری شکایت‌های باز', 'count' => $openComplaintsCount, 'route' => 'admin.complaints.index', 'icon' => 'complaint'] : null,
            $canContact && $unreadContactMessagesCount > 0 ? ['title' => 'پاسخ به پیام‌های جدید', 'count' => $unreadContactMessagesCount, 'route' => 'admin.contact_messages.index', 'icon' => 'mail'] : null,
        ]));

        $shortcuts = array_values(array_filter([
            $can('posts.create') ? ['title' => 'خبر جدید', 'route' => 'admin.posts.create', 'icon' => 'news'] : null,
            $can('unions.create') ? ['title' => 'اتحادیه جدید', 'route' => 'admin.unions.create', 'icon' => 'building'] : null,
            $can('pages.create') ? ['title' => 'صفحه جدید', 'route' => 'admin.pages.create', 'icon' => 'file'] : null,
            $can('messages.send') ? ['title' => 'ارسال پیام', 'route' => 'admin.messages.create', 'icon' => 'mail'] : null,
            $can('announcements.create') ? ['title' => 'اطلاعیه جدید', 'route' => 'admin.announcements.create', 'icon' => 'check'] : null,
        ]));

        return view('admin.dashboard', [
            'stats' => $stats,
            'tasks' => $tasks,
            'shortcuts' => $shortcuts,
            'pendingApprovals' => $pendingApprovals->take(6)->values(),
            'canReview' => $canReview,
            'privateAnnouncements' => Announcement::query()
                ->privateVisibleTo($user)
                ->with('union')
                ->orderBy('sort_order')
                ->latest('published_at')
                ->take(5)
                ->get(),
            'systemStatus' => $user->hasRole('super-admin') ? [
                'site' => config('app.debug') ? 'حالت توسعه' : 'فعال',
                'database' => $this->databaseStatus(),
                'sms' => $this->smsStatus($latestSmsLog),
                'latest_sms' => $latestSmsLog?->created_at,
                'published_this_month' => $this->publishedThisMonthCount($approvalService),
            ] : null,
        ]);
    }

    private function databaseStatus(): string
    {
        try {
            DB::connection()->getPdo();

            return 'متصل';
        } catch (\Throwable) {
            return 'قطع';
        }
    }

    private function smsStatus(?SmsLog $latestSmsLog): string
    {
        if (! $latestSmsLog) {
            return 'بدون سابقه ارسال';
        }

        return match ($latestSmsLog->status) {
            'sent' => 'آخرین ارسال موفق',
            'pending' => 'دارای ارسال در انتظار',
            'partial' => 'آخرین ارسال ناقص',
            'failed' => 'آخرین ارسال ناموفق',
            default => $latestSmsLog->status,
        };
    }

    private function publishedThisMonthCount(ContentApprovalService $approvalService): int
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        return collect($approvalService->contentTypes())->sum(function (array $definition) use ($startOfMonth, $endOfMonth): int {
            /** @var class-string<\Illuminate\Database\Eloquent\Model> $model */
            $model = $definition['model'];
            $table = (new $model())->getTable();

            if (! Schema::hasColumn($table, 'status')) {
                return 0;
            }

            $dateColumn = Schema::hasColumn($table, 'published_at') ? 'published_at' : 'created_at';

            return $model::query()
                ->where('status', 'published')
                ->when(Schema::hasColumn($table, 'is_active'), fn (Builder $query) => $query->where('is_active', true))
                ->whereBetween($dateColumn, [$startOfMonth, $endOfMonth])
                ->count();
        });
    }
}
