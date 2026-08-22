@extends('frontend.layouts.app')
@section('title', $commission->title.' | کمیسیون‌ها')
@section('meta_description', plain_text($commission->description, 160))
@section('frontend_variant', 'compact')
@section('footer_links_variant', 'short')

@section('content')
<section class="page-header page-header-alt commissions-hero commission-detail-hero">
    <div class="site-container">
        <span class="commissions-eyebrow">جزئیات کمیسیون</span>
        <h1>{{ $commission->title }}</h1>
        <p>{{ plain_text($commission->description, 180) ?: 'اطلاعات تکمیلی این کمیسیون در حال تکمیل است.' }}</p>
        <nav class="breadcrumb">
            <a href="{{ route('home') }}">خانه</a>
            <a href="{{ route('commissions.index') }}">کمیسیون‌ها</a>
            <span>{{ $commission->title }}</span>
        </nav>
    </div>
</section>

<section class="site-container commission-detail-page">
    <div class="commission-detail-layout">
        <article class="commission-detail-main">
            @if($commission->image)
                <figure class="commission-detail-cover">
                    <img src="{{ $commission->image_url }}" alt="{{ $commission->title }}" loading="eager" fetchpriority="high" decoding="async">
                </figure>
            @endif

            <div class="commission-detail-card commission-description-card">
                <span class="commissions-eyebrow">معرفی</span>
                <h2>{{ $commission->title }}</h2>
                <div class="commission-rich-text">{!! $commission->description ?: '<p>توضیحات این کمیسیون هنوز تکمیل نشده است.</p>' !!}</div>
            </div>

            <div class="commission-detail-card">
                <div class="commission-section-title">
                    <span>وظایف کمیسیون</span>
                    <small>{{ $commission->activeTasks->count() }} مورد</small>
                </div>
                <div class="commission-task-list">
                    @forelse($commission->activeTasks as $task)
                        <div class="commission-task-card">
                            <strong>{{ $task->title }}</strong>
                            <div>{!! $task->description ?: '<p>توضیحی برای این وظیفه ثبت نشده است.</p>' !!}</div>
                        </div>
                    @empty
                        <p class="text-muted">وظیفه‌ای برای این کمیسیون ثبت نشده است.</p>
                    @endforelse
                </div>
            </div>

            <div class="commission-detail-card">
                <div class="commission-section-title">
                    <span>جلسات کمیسیون</span>
                    <small>{{ $commission->publishedSessions->count() }} جلسه</small>
                </div>
                <div class="commission-session-list">
                    @forelse($commission->publishedSessions as $session)
                        <div class="commission-session-card">
                            <div>
                                <strong>{{ $session->title }}</strong>
                                <span>{{ jalali_datetime($session->session_date) ?: 'بدون تاریخ' }}</span>
                                <p>{{ plain_text($session->description, 150) ?: 'شرحی برای این جلسه ثبت نشده است.' }}</p>
                            </div>
                            @if($session->minutes_file)
                                <a class="commission-download-link" href="{{ \App\Support\PublicFileUrl::make($session->minutes_file, '') }}" target="_blank">دانلود صورتجلسه</a>
                            @endif
                        </div>
                    @empty
                        <p class="text-muted">جلسه منتشرشده‌ای برای این کمیسیون وجود ندارد.</p>
                    @endforelse
                </div>
            </div>
        </article>

        <aside class="commission-detail-sidebar">
            <div class="commission-sidebar-card">
                <h3>اطلاعات کمیسیون</h3>
                <div class="commission-stat-row"><span>تعداد اعضا</span><strong>{{ count($commission->members ?? []) }}</strong></div>
                <div class="commission-stat-row"><span>تعداد وظایف</span><strong>{{ $commission->activeTasks->count() }}</strong></div>
                <div class="commission-stat-row"><span>جلسات منتشرشده</span><strong>{{ $commission->publishedSessions->count() }}</strong></div>
                <a class="commission-back-link" href="{{ route('commissions.index') }}">بازگشت به فهرست کمیسیون‌ها</a>
            </div>

            <div class="commission-sidebar-card">
                <h3>اعضای کمیسیون</h3>
                <div class="commission-member-list">
                    @forelse($commission->members ?? [] as $member)
                        <span>{{ $member['name'] ?? '' }}</span>
                    @empty
                        <p class="text-muted">اعضایی برای نمایش ثبت نشده است.</p>
                    @endforelse
                </div>
            </div>

            @if(!empty($commission->attachments))
                <div class="commission-sidebar-card">
                    <h3>پیوست‌ها</h3>
                    <div class="commission-attachment-list">
                        @foreach($commission->attachments as $file)
                            <a href="{{ \App\Support\PublicFileUrl::make($file['path'] ?? '', '') }}" target="_blank">{{ $file['name'] ?? 'دانلود فایل' }}</a>
                        @endforeach
                    </div>
                </div>
            @endif
        </aside>
    </div>
</section>
@endsection
