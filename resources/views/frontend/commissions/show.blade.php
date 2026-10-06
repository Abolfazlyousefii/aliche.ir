@extends('frontend.layouts.app')
@section('title', $commission->title.' | کمیسیون‌ها')
@section('meta_description', plain_text($commission->description, 160))
@section('frontend_variant', 'compact')
@section('footer_links_variant', 'short')

@section('content')
@php
    $tasks = $commission->activeTasks;
    $sessions = $commission->publishedSessions;
    $memberNames = collect($commission->members ?? [])
        ->map(fn ($member) => trim((string) (is_array($member) ? ($member['name'] ?? '') : $member)))
        ->filter()
        ->values();
    $attachments = collect($commission->attachments ?? [])
        ->filter(fn ($file) => is_array($file) && filled($file['path'] ?? null))
        ->values();
    $hasDescription = filled(plain_text($commission->description));
    // Sanitize on output too: old records may predate the admin-side sanitizer.
    $richTextSanitizer = app(\App\Services\RichTextSanitizer::class);
@endphp

<main class="commission-profile-v3">
    <header class="commission-profile-v3__header">
        <div class="site-container">
            <nav class="commission-profile-v3__breadcrumb" aria-label="مسیر صفحه">
                <a href="{{ route('home') }}">خانه</a>
                <span aria-hidden="true">/</span>
                <a href="{{ route('commissions.index') }}">کمیسیون‌ها</a>
                <span aria-hidden="true">/</span>
                <span aria-current="page">{{ $commission->title }}</span>
            </nav>
            <span class="commission-profile-v3__eyebrow">کمیسیون تخصصی اتاق اصناف</span>
            <h1>{{ $commission->title }}</h1>
            <p>معرفی، اعضا، وظایف و جلسات منتشرشده این کمیسیون</p>
        </div>
    </header>

    <div class="site-container commission-profile-v3__body">
        <div class="commission-profile-v3__layout">
            <div class="commission-profile-v3__main">
                <section class="commission-profile-v3__overview" aria-labelledby="commission-overview-title">
                    <div class="commission-profile-v3__overview-content">
                        <div class="commission-profile-v3__section-title">
                            <span class="commission-profile-v3__eyebrow">آشنایی با کمیسیون</span>
                            <h2 id="commission-overview-title">معرفی کمیسیون</h2>
                        </div>
                        @if($hasDescription)
                            <div class="commission-profile-v3__rich-text">
                                {!! $richTextSanitizer->sanitize($commission->description) !!}
                            </div>
                        @else
                            <p class="commission-profile-v3__muted">توضیحات این کمیسیون هنوز در پنل مدیریت تکمیل نشده است.</p>
                        @endif
                    </div>
                    @if($commission->image)
                        <figure class="commission-profile-v3__thumbnail">
                            <img src="{{ $commission->image_url }}" alt="تصویر {{ $commission->title }}" loading="eager" decoding="async">
                        </figure>
                    @endif
                </section>

                @if($tasks->isNotEmpty())
                    <section class="commission-profile-v3__section" aria-labelledby="commission-tasks-title">
                        <div class="commission-profile-v3__section-head">
                            <div>
                                <span class="commission-profile-v3__eyebrow">حوزه فعالیت</span>
                                <h2 id="commission-tasks-title">وظایف کمیسیون</h2>
                            </div>
                            <span class="commission-profile-v3__count">{{ fa_number($tasks->count()) }} وظیفه</span>
                        </div>
                        <div class="commission-profile-v3__tasks">
                            @foreach($tasks as $task)
                                <article class="commission-profile-v3__task">
                                    <div class="commission-profile-v3__task-icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24"><path d="M5 12.5l4.5 4.5L19 7"/></svg>
                                    </div>
                                    <div>
                                        <h3>{{ $task->title }}</h3>
                                        @if(filled(plain_text($task->description)))
                                            <div class="commission-profile-v3__rich-text">
                                                {!! $richTextSanitizer->sanitize($task->description) !!}
                                            </div>
                                        @endif
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if($sessions->isNotEmpty())
                    <section class="commission-profile-v3__section" aria-labelledby="commission-sessions-title">
                        <div class="commission-profile-v3__section-head">
                            <div>
                                <span class="commission-profile-v3__eyebrow">گزارش فعالیت</span>
                                <h2 id="commission-sessions-title">جلسات منتشرشده</h2>
                            </div>
                            <span class="commission-profile-v3__count">{{ fa_number($sessions->count()) }} جلسه</span>
                        </div>
                        <div class="commission-profile-v3__sessions">
                            @foreach($sessions as $session)
                                <article class="commission-profile-v3__session">
                                    <div class="commission-profile-v3__session-top">
                                        <h3>{{ $session->title }}</h3>
                                        @if($session->session_date)
                                            <time datetime="{{ $session->session_date->toDateString() }}">
                                                {{ jalali_datetime($session->session_date) }}
                                            </time>
                                        @endif
                                    </div>
                                    @if(filled(plain_text($session->description)))
                                        <p>{{ plain_text($session->description, 220) }}</p>
                                    @endif

                                    @php
                                        $sessionAttachments = collect($session->attachments ?? [])
                                            ->filter(fn ($file) => is_array($file) && filled($file['path'] ?? null));
                                        $sessionImages = collect($session->images ?? [])
                                            ->filter(fn ($file) => is_array($file) && filled($file['path'] ?? null));
                                    @endphp

                                    @if($session->minutes_file || $sessionAttachments->isNotEmpty())
                                        <div class="commission-profile-v3__files">
                                            @if($session->minutes_file)
                                                <a href="{{ \App\Support\PublicFileUrl::make($session->minutes_file, '') }}" target="_blank" rel="noopener noreferrer">دریافت صورتجلسه</a>
                                            @endif
                                            @foreach($sessionAttachments as $file)
                                                <a href="{{ \App\Support\PublicFileUrl::make($file['path'], '') }}" target="_blank" rel="noopener noreferrer">{{ $file['name'] ?? 'دانلود پیوست جلسه' }}</a>
                                            @endforeach
                                        </div>
                                    @endif
                                    @if($sessionImages->isNotEmpty())
                                        <div class="commission-profile-v3__session-images">
                                            @foreach($sessionImages as $image)
                                                <a href="{{ \App\Support\PublicFileUrl::make($image['path'], '') }}" target="_blank" rel="noopener noreferrer" aria-label="مشاهده تصویر جلسه">
                                                    <img src="{{ \App\Support\PublicFileUrl::make($image['path'], '') }}" alt="{{ $image['name'] ?? $session->title }}" loading="lazy" decoding="async">
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>

            <aside class="commission-profile-v3__sidebar" aria-label="اطلاعات تکمیلی کمیسیون">
                <section class="commission-profile-v3__side-card">
                    <h2>اطلاعات کمیسیون</h2>
                    <dl class="commission-profile-v3__stats">
                        <div><dt>اعضای ثبت‌شده</dt><dd>{{ fa_number($memberNames->count()) }}</dd></div>
                        <div><dt>وظایف فعال</dt><dd>{{ fa_number($tasks->count()) }}</dd></div>
                        <div><dt>جلسات منتشرشده</dt><dd>{{ fa_number($sessions->count()) }}</dd></div>
                    </dl>
                    <a class="commission-profile-v3__back" href="{{ route('commissions.index') }}">
                        <span>بازگشت به فهرست کمیسیون‌ها</span>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 5l-7 7 7 7"/></svg>
                    </a>
                </section>

                @if($memberNames->isNotEmpty())
                    <section class="commission-profile-v3__side-card">
                        <h2>اعضای کمیسیون</h2>
                        <ul class="commission-profile-v3__members">
                            @foreach($memberNames as $name)
                                <li>{{ $name }}</li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                @if($attachments->isNotEmpty())
                    <section class="commission-profile-v3__side-card">
                        <h2>پیوست‌های کمیسیون</h2>
                        <div class="commission-profile-v3__attachments">
                            @foreach($attachments as $file)
                                <a href="{{ \App\Support\PublicFileUrl::make($file['path'], '') }}" target="_blank" rel="noopener noreferrer">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 4h7l4 4v12H8zM15 4v5h4M11 14h5M11 17h4"/></svg>
                                    <span>{{ $file['name'] ?? 'دانلود پیوست' }}</span>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif
            </aside>
        </div>
    </div>
</main>
@endsection
