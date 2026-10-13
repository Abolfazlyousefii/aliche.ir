@extends('frontend.layouts.app')
@section('title', ($page->meta_title ?: $page->title).' | اتاق اصناف مرکز استان گلستان')
@section('meta_description', $page->meta_description ?: plain_text($page->excerpt ?: $page->body, 160))
@section('content')
<main class="chamber-board-page"><div class="site-container chamber-board-container">
    <nav class="chamber-breadcrumb" aria-label="مسیر صفحه"><a href="{{ route('home') }}">خانه</a><span>/</span><span>{{ $page->title }}</span></nav>
    <header class="chamber-intro"><h1>{{ $page->title }}</h1>
        @if($page->excerpt)<div class="chamber-intro-excerpt">{!! $page->excerpt !!}</div>@endif
        @if($page->body)<div class="chamber-intro-body">{!! $page->body !!}</div>@endif
        @if($page->featured_image)<img class="chamber-page-image" src="{{ image_url($page->featured_image) }}" alt="{{ $page->title }}" loading="lazy">@endif
    </header>
    @if($president)<section class="chamber-president" aria-label="رئیس اتاق اصناف">
        @include('frontend.pages.chamber-member-photo', ['member' => $president])
        <span class="chamber-president-label">رئیس اتاق اصناف</span><h2>{{ $president->full_name }}</h2><p class="chamber-position">{{ $president->position }}</p>
        <button class="chamber-profile-action" type="button" data-chamber-member="{{ $president->id }}" aria-haspopup="dialog">آشنایی با رئیس اتاق <span aria-hidden="true">↖</span></button>
    </section>@endif
    @if($members->isNotEmpty())<section class="chamber-other-members" aria-labelledby="chamber-members-heading">
        <div class="chamber-members-heading"><h2 id="chamber-members-heading">اعضای هیئت‌رئیسه</h2><span>همراه در خدمت‌رسانی به جامعهٔ اصناف</span></div>
        <div class="chamber-members-grid">@foreach($members as $member)
            <button class="chamber-board-member-card" type="button" data-chamber-member="{{ $member->id }}" aria-haspopup="dialog" aria-label="معرفی {{ $member->full_name }}">
                @include('frontend.pages.chamber-member-photo')
                <span class="chamber-member-caption"><span><strong>{{ $member->full_name }}</strong><span class="chamber-position">{{ $member->position }}</span></span><span aria-hidden="true">↖</span></span>
            </button>
        @endforeach</div>
    </section>@endif
</div>
@foreach($president ? $members->prepend($president) : $members as $member)
    <template data-chamber-profile="{{ $member->id }}">
        @include('frontend.pages.chamber-member-photo')
        <h2 id="chamber-dialog-name">{{ $member->full_name }}</h2><p class="chamber-position">{{ $member->position }}</p>
        @if($member->bio)<p class="chamber-member-bio">{{ $member->bio }}</p>@endif
    </template>
@endforeach
<dialog id="chamber-member-dialog" class="chamber-member-dialog" aria-labelledby="chamber-dialog-name">
    <button type="button" class="chamber-dialog-close" aria-label="بستن معرفی عضو" data-chamber-close>×</button><div data-chamber-dialog-content></div>
</dialog></main>
@endsection
@push('styles')<link rel="stylesheet" href="{{ asset('assets/frontend/css/chamber-board.css') }}?v={{ filemtime(public_path('assets/frontend/css/chamber-board.css')) }}">@endpush
@push('scripts')<script src="{{ asset('assets/frontend/js/chamber-board.js') }}?v={{ filemtime(public_path('assets/frontend/js/chamber-board.js')) }}" defer></script>@endpush
