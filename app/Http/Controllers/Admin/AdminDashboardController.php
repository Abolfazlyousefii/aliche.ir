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
        $pendingApprovals = $approvalService->pendingItems();

        $openComplaintsQuery = Complaint::query()
            ->visibleTo($user)
            ->whereIn('status', ['registered', 'reviewing', 'need_more_info']);

        $smsQuery = SmsLog::query()->visibleTo($user);
        $latestSmsLog = (clone $smsQuery)->latest()->first();

        $publishedThisMonthCount = $this->publishedThisMonthCount($approvalService);
        $openComplaintsCount = (clone $openComplaintsQuery)->count();
        $unreadContactMessagesCount = ContactMessage::query()->unread()->count();

        return view('admin.dashboard', [
            'pendingApprovals' => $pendingApprovals->take(8)->values(),
            'pendingApprovalsCount' => $pendingApprovals->count(),
            'unreadContactMessagesCount' => $unreadContactMessagesCount,
            'openComplaintsCount' => $openComplaintsCount,
            'unionsCount' => GuildUnion::query()->where('is_active', true)->count(),
            'membersCount' => UnionMember::query()->visibleTo($user)->where('is_active', true)->count(),
            'sentSmsRecipientCount' => (clone $smsQuery)->where('status', 'sent')->sum('recipient_count'),
            'privateAnnouncements' => Announcement::query()
                ->privateVisibleTo($user)
                ->with('union')
                ->orderBy('sort_order')
                ->latest('published_at')
                ->take(5)
                ->get(),
            'dashboardTasks' => $this->dashboardTasks(
                $pendingApprovals->count(),
                $openComplaintsCount,
                $unreadContactMessagesCount,
            ),
            'systemStatus' => [
                'site' => config('app.debug') ? 'حالت توسعه' : 'فعال',
                'database' => $this->databaseStatus(),
                'sms' => $this->smsStatus($latestSmsLog),
                'latest_sms' => $latestSmsLog?->created_at,
                'published_this_month' => $publishedThisMonthCount,
            ],
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function dashboardTasks(int $pendingApprovalsCount, int $openComplaintsCount, int $unreadContactMessagesCount): array
    {
        return collect([
            $pendingApprovalsCount > 0 ? "بررسی و تعیین تکلیف {$pendingApprovalsCount} محتوای در انتظار تایید" : null,
            $openComplaintsCount > 0 ? "پیگیری {$openComplaintsCount} شکایت باز" : null,
            $unreadContactMessagesCount > 0 ? "بازبینی {$unreadContactMessagesCount} پیام تماس خوانده‌نشده" : null,
        ])->filter()->values()->whenEmpty(fn ($tasks) => $tasks->push('مورد فوری برای اقدام در داشبورد ثبت نشده است.'))->all();
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
