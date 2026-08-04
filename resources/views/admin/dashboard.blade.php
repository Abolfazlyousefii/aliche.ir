@extends('admin.layouts.app')

@section('title', 'داشبورد مدیریت')

@section('content')
<section class="admin-welcome-card">
    <div>
        <p class="admin-eyebrow">نمای کلی امروز</p>
        <h2>به پنل مدیریت اتاق اصناف مرکز استان گلستان خوش آمدید</h2>
        <p>از این بخش می‌توانید وضعیت محتوای سایت، پیام‌ها، شکایات و فعالیت‌های مهم را به‌صورت خلاصه مشاهده کنید.</p>
    </div>
    <div class="admin-date-card">
        <span>امروز</span>
        <strong>{{ jalali_text_date(now('Asia/Tehran')) }}</strong>
    </div>
</section>

<section class="admin-stats-grid" aria-label="آمار کلی پنل مدیریت">
    <article class="admin-stat-card stat-warning">
        <div class="admin-stat-icon">✅</div>
        <div>
            <span>محتواهای در انتظار تایید</span>
            <strong>{{ number_format($pendingApprovalsCount) }}</strong>
        </div>
    </article>

    <article class="admin-stat-card stat-danger">
        <div class="admin-stat-icon">📨</div>
        <div>
            <span>شکایت‌های جدید</span>
            <strong>{{ number_format($openComplaintsCount) }}</strong>
        </div>
    </article>

    <article class="admin-stat-card stat-primary">
        <div class="admin-stat-icon">🏢</div>
        <div>
            <span>تعداد اتحادیه‌ها</span>
            <strong>{{ number_format($unionsCount) }}</strong>
        </div>
    </article>

    <article class="admin-stat-card stat-success">
        <div class="admin-stat-icon">🤝</div>
        <div>
            <span>تعداد اعضا</span>
            <strong>{{ number_format($membersCount) }}</strong>
        </div>
    </article>

    <article class="admin-stat-card stat-info">
        <div class="admin-stat-icon">☎️</div>
        <div>
            <span>پیام‌های تماس خوانده‌نشده</span>
            <strong>{{ number_format($unreadContactMessagesCount) }}</strong>
        </div>
    </article>

    <article class="admin-stat-card stat-purple">
        <div class="admin-stat-icon">💬</div>
        <div>
            <span>پیامک‌های ارسال‌شده</span>
            <strong>{{ number_format($sentSmsRecipientCount) }}</strong>
        </div>
    </article>
</section>

<section class="admin-dashboard-grid">
    <div class="admin-panel-card">
        <div class="admin-panel-header">
            <h3>کارهای پیشنهادی امروز</h3>
            <span>اولویت‌دار</span>
        </div>
        <ul class="admin-task-list">
            @foreach($dashboardTasks as $task)
                <li><span></span>{{ $task }}</li>
            @endforeach
        </ul>
    </div>


    <div class="admin-panel-card">
        <div class="admin-panel-header">
            <h3>محتواهای در انتظار تایید</h3>
            <a href="{{ route('admin.pending_approvals.index') }}" class="btn btn-sm btn-outline-primary">مشاهده همه</a>
        </div>
        @if($pendingApprovals->isNotEmpty())
            <div class="admin-status-list">
                @foreach($pendingApprovals as $item)
                    <div>
                        <span>{{ $item['label'] }} - {{ $item['title'] }}</span>
                        @if($item['show_url'])
                            <a href="{{ $item['show_url'] }}" class="btn btn-sm btn-light">مشاهده</a>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-muted mb-0">در حال حاضر محتوایی برای تایید وجود ندارد.</p>
        @endif
    </div>



    <div class="admin-panel-card">
        <div class="admin-panel-header">
            <h3>اطلاعیه‌های خصوصی</h3>
            <span>ویژه کاربران پنل</span>
        </div>
        @if($privateAnnouncements->isNotEmpty())
            <div class="admin-status-list">
                @foreach($privateAnnouncements as $announcement)
                    <div>
                        <span>{{ $announcement->title }} @if($announcement->union) - {{ $announcement->union->display_title }} @endif</span>
                        <small>{{ jalali_datetime($announcement->published_at) ?: jalali_datetime($announcement->starts_at) }}</small>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-muted mb-0">اطلاعیه خصوصی جدیدی برای شما ثبت نشده است.</p>
        @endif
    </div>

    <div class="admin-panel-card">
        <div class="admin-panel-header">
            <h3>آخرین وضعیت سامانه</h3>
            <span>به‌روزرسانی روزانه</span>
        </div>
        <div class="admin-status-list">
            <div><span>وضعیت سایت</span><strong class="{{ $systemStatus['site'] === 'فعال' ? 'text-success' : 'text-warning' }}">{{ $systemStatus['site'] }}</strong></div>
            <div><span>وضعیت پایگاه داده</span><strong class="{{ $systemStatus['database'] === 'متصل' ? 'text-success' : 'text-danger' }}">{{ $systemStatus['database'] }}</strong></div>
            <div><span>وضعیت پیامک</span><strong class="{{ $systemStatus['sms'] === 'آخرین ارسال موفق' ? 'text-success' : 'text-warning' }}">{{ $systemStatus['sms'] }}</strong></div>
            <div><span>آخرین گزارش پیامک</span><strong>{{ $systemStatus['latest_sms'] ? jalali_datetime($systemStatus['latest_sms']) : 'ثبت نشده' }}</strong></div>
            <div><span>محتوای منتشرشده این ماه</span><strong>{{ number_format($systemStatus['published_this_month']) }} مورد</strong></div>
        </div>
    </div>
</section>
@endsection
