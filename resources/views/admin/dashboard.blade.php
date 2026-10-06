@extends('admin.layouts.app')

@section('title', 'پیشخوان مدیریت')

@section('content')
<div class="admin-dashboard-v2">
    <section class="admin-dashboard-intro" aria-labelledby="admin-dashboard-title">
        <div class="admin-dashboard-intro__copy">
            <span class="admin-dashboard-kicker">مرکز مدیریت اتاق اصناف</span>
            <h2 id="admin-dashboard-title">خوش آمدید، {{ auth()->user()?->name ?? 'مدیر سامانه' }}</h2>
            <p>کارهای مهم را ببینید، سریع وارد بخش موردنظر شوید و فعالیت‌های سایت را پیگیری کنید.</p>
            <div class="admin-dashboard-intro__actions">
                @if(count($tasks) > 0)
                    <a class="admin-dashboard-start" href="{{ route($tasks[0]['route']) }}">
                        @include('admin.components.icon', ['name' => 'check'])
                        <span>شروع رسیدگی</span>
                    </a>
                @elseif(count($shortcuts) > 0)
                    <a class="admin-dashboard-start" href="{{ route($shortcuts[0]['route']) }}">
                        @include('admin.components.icon', ['name' => $shortcuts[0]['icon']])
                        <span>{{ $shortcuts[0]['title'] }}</span>
                    </a>
                @endif
                <a class="admin-dashboard-website" href="{{ route('home') }}" target="_blank" rel="noopener noreferrer">
                    مشاهده سایت
                    @include('admin.components.icon', ['name' => 'external'])
                </a>
            </div>
        </div>
        <div class="admin-dashboard-intro__date" aria-label="تاریخ امروز">
            <span>امروز</span>
            <strong>{{ jalali_text_date(now('Asia/Tehran')) }}</strong>
            <small>نمای کلی مدیریت</small>
        </div>
    </section>

    @if(count($stats) > 0)
        <section class="admin-dashboard-metrics" aria-label="شاخص‌های قابل دسترس">
            @foreach($stats as $stat)
                <a class="admin-metric-card admin-metric-card--{{ $stat['tone'] }}" href="{{ route($stat['route']) }}">
                    <span class="admin-metric-card__icon">@include('admin.components.icon', ['name' => $stat['icon']])</span>
                    <span class="admin-metric-card__body">
                        <span class="admin-metric-card__label">{{ $stat['title'] }}</span>
                        <strong>{{ fa_number(number_format($stat['count'])) }}</strong>
                        <small>{{ $stat['hint'] }}</small>
                    </span>
                    <span class="admin-metric-card__arrow" aria-hidden="true">←</span>
                </a>
            @endforeach
        </section>
    @endif

    <section class="admin-dashboard-main-grid" aria-label="اقدام‌های مدیریت">
        <div class="admin-dashboard-v2-panel admin-dashboard-v2-panel--tasks">
            <div class="admin-dashboard-v2-panel__head">
                <div>
                    <span class="admin-dashboard-panel-eyebrow">اولویت‌های روز</span>
                    <h3>نیازمند رسیدگی</h3>
                </div>
                <span class="admin-dashboard-v2-pill">{{ fa_number(count($tasks)) }} کار</span>
            </div>
            @if(count($tasks) > 0)
                <div class="admin-dashboard-action-list">
                    @foreach($tasks as $task)
                        <a href="{{ route($task['route']) }}" class="admin-dashboard-action">
                            <span class="admin-dashboard-action__icon">@include('admin.components.icon', ['name' => $task['icon']])</span>
                            <span class="admin-dashboard-action__title">{{ $task['title'] }}</span>
                            <span class="admin-dashboard-action__count">{{ fa_number($task['count']) }}</span>
                            <span class="admin-dashboard-action__arrow" aria-hidden="true">←</span>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="admin-dashboard-empty">
                    @include('admin.components.icon', ['name' => 'check'])
                    <strong>مورد فوری ثبت نشده است</strong>
                    <p>در حال حاضر کار نیازمند اقدام در بخش‌های قابل دسترس شما وجود ندارد.</p>
                </div>
            @endif
        </div>

        <div class="admin-dashboard-v2-panel admin-dashboard-v2-panel--shortcuts">
            <div class="admin-dashboard-v2-panel__head">
                <div>
                    <span class="admin-dashboard-panel-eyebrow">دسترسی سریع</span>
                    <h3>شروع یک کار جدید</h3>
                </div>
            </div>
            @if(count($shortcuts) > 0)
                <div class="admin-dashboard-shortcuts">
                    @foreach($shortcuts as $shortcut)
                        <a href="{{ route($shortcut['route']) }}" class="admin-dashboard-shortcut">
                            <span class="admin-dashboard-shortcut__icon">@include('admin.components.icon', ['name' => $shortcut['icon']])</span>
                            <span>{{ $shortcut['title'] }}</span>
                            <span class="admin-dashboard-shortcut__arrow" aria-hidden="true">←</span>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="admin-dashboard-empty">
                    <p>فعلاً مجوز ایجاد محتوای جدید برای این حساب فعال نیست.</p>
                </div>
            @endif
        </div>
    </section>

    <section class="admin-dashboard-secondary-grid" aria-label="فعالیت‌ها و وضعیت">
        @if($canReview)
            <div class="admin-dashboard-v2-panel">
                <div class="admin-dashboard-v2-panel__head">
                    <div>
                        <span class="admin-dashboard-panel-eyebrow">گردش کار انتشار</span>
                        <h3>آخرین محتوای در انتظار</h3>
                    </div>
                    <a class="admin-dashboard-panel-link" href="{{ route('admin.pending_approvals.index') }}">مشاهده همه <span aria-hidden="true">←</span></a>
                </div>
                @forelse($pendingApprovals as $item)
                    <div class="admin-dashboard-activity">
                        <span class="admin-dashboard-activity__tag">{{ $item['label'] }}</span>
                        <div class="admin-dashboard-activity__body">
                            <strong>{{ $item['title'] }}</strong>
                            <small>{{ jalali_datetime($item['created_at'] ?? null) ?: 'تاریخ ثبت نشده' }}</small>
                        </div>
                    </div>
                @empty
                    <div class="admin-dashboard-empty admin-dashboard-empty--compact">
                        <p>محتوایی در انتظار تأیید ندارید.</p>
                    </div>
                @endforelse
            </div>
        @endif

        <div class="admin-dashboard-v2-panel">
            <div class="admin-dashboard-v2-panel__head">
                <div>
                    <span class="admin-dashboard-panel-eyebrow">ویژه کاربران مجاز</span>
                    <h3>اطلاعیه‌های داخلی</h3>
                </div>
            </div>
            @forelse($privateAnnouncements as $announcement)
                <div class="admin-dashboard-activity">
                    <span class="admin-dashboard-activity__dot" aria-hidden="true"></span>
                    <div class="admin-dashboard-activity__body">
                        <strong>{{ $announcement->title }}</strong>
                        <small>
                            @if($announcement->union){{ $announcement->union->display_title }} · @endif
                            {{ jalali_datetime($announcement->published_at) ?: jalali_datetime($announcement->starts_at) ?: 'بدون تاریخ' }}
                        </small>
                    </div>
                </div>
            @empty
                <div class="admin-dashboard-empty admin-dashboard-empty--compact">
                    <p>اطلاعیه داخلی جدیدی برای شما ثبت نشده است.</p>
                </div>
            @endforelse
        </div>

        @if($systemStatus)
            <div class="admin-dashboard-v2-panel admin-dashboard-v2-panel--system">
                <div class="admin-dashboard-v2-panel__head">
                    <div>
                        <span class="admin-dashboard-panel-eyebrow">ویژه مدیرکل</span>
                        <h3>وضعیت سامانه</h3>
                    </div>
                </div>
                <dl class="admin-dashboard-health">
                    <div><dt>وب‌سایت</dt><dd>{{ $systemStatus['site'] }}</dd></div>
                    <div><dt>پایگاه داده</dt><dd>{{ $systemStatus['database'] }}</dd></div>
                    <div><dt>سامانه پیامک</dt><dd>{{ $systemStatus['sms'] }}</dd></div>
                    <div><dt>آخرین گزارش پیامک</dt><dd>{{ $systemStatus['latest_sms'] ? jalali_datetime($systemStatus['latest_sms']) : 'ثبت نشده' }}</dd></div>
                    <div><dt>انتشار این ماه</dt><dd>{{ fa_number(number_format($systemStatus['published_this_month'])) }} مورد</dd></div>
                </dl>
            </div>
        @endif
    </section>
</div>
@endsection
