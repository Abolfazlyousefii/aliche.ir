@extends('frontend.layouts.app')

@section('title', ($place->title ?? 'مکان گردشگری') . ' | گردشگری گرگان')
@section('meta_description', plain_text($place->short_description ?: ($place->description ?? ''), 160))
@section('footer_links_variant', 'short')

@section('content')
@php
    $title = $place->title ?? 'مکان گردشگری';
    $categoryTitle = $place->category?->title ?: 'گردشگری گرگان';
    $summary = plain_text($place->short_description ?: $place->description, 220);
    $featuredImage = $place->home_image_url;
    $galleryImages = collect($place->gallery_items ?? [])
        ->filter(fn ($image) => filled($image['url'] ?? null))
        ->values();

    if ($galleryImages->isEmpty()) {
        $galleryImages = collect([['url' => $featuredImage, 'caption' => $title]]);
    }

    $latitude = filled($place->latitude) ? (string) $place->latitude : null;
    $longitude = filled($place->longitude) ? (string) $place->longitude : null;
    $coordinates = $latitude && $longitude ? $latitude.', '.$longitude : null;
    $mapUrl = filled($place->map_url) ? (string) $place->map_url : null;
    $coordinateMapUrl = $coordinates ? 'https://www.google.com/maps/search/?api=1&query='.urlencode($coordinates) : null;
    $mapLink = $mapUrl ?: $coordinateMapUrl;
    $isEmbeddableMap = $mapUrl && str_contains($mapUrl, 'embed');
    $phoneHref = filled($place->phone) ? 'tel:'.preg_replace('/[^0-9+]/', '', (string) $place->phone) : null;

    $visitInfoCards = [
        ['icon' => '📍', 'title' => 'آدرس', 'value' => $place->address ?: 'آدرس این مکان هنوز ثبت نشده است.', 'link' => $mapLink, 'linkLabel' => $mapLink ? 'مشاهده روی نقشه' : null],
        ['icon' => '⏰', 'title' => 'ساعت بازدید', 'value' => $place->working_hours ?: 'ساعت بازدید هنوز ثبت نشده است.', 'link' => null, 'linkLabel' => null],
        ['icon' => '💳', 'title' => 'هزینه بازدید', 'value' => $place->visit_price ?: 'هزینه بازدید هنوز ثبت نشده است.', 'link' => null, 'linkLabel' => null],
        ['icon' => '☎️', 'title' => 'تلفن تماس', 'value' => $place->phone ?: 'شماره تماس ثبت نشده است.', 'link' => $phoneHref, 'linkLabel' => $phoneHref ? 'تماس مستقیم' : null],
        ['icon' => '🧭', 'title' => 'مختصات', 'value' => $coordinates ?: 'مختصات جغرافیایی ثبت نشده است.', 'link' => $coordinateMapUrl, 'linkLabel' => $coordinateMapUrl ? 'مسیریابی' : null, 'dir' => $coordinates ? 'ltr' : 'rtl'],
        ['icon' => '🗺️', 'title' => 'نقشه', 'value' => $mapLink ? 'لینک نقشه برای این مکان ثبت شده است.' : 'لینک نقشه هنوز ثبت نشده است.', 'link' => $mapLink, 'linkLabel' => $mapLink ? 'باز کردن نقشه' : null],
    ];
@endphp

<main class="bg-light">
    <section class="py-5 bg-white border-bottom">
        <div class="container">
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a class="text-decoration-none" href="{{ route('home') }}">خانه</a></li>
                    <li class="breadcrumb-item"><a class="text-decoration-none" href="{{ route('tourism.index') }}">گردشگری</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $title }}</li>
                </ol>
            </nav>

            <div class="row g-4 align-items-center">
                <div class="col-lg-6">
                    <span class="badge text-bg-success rounded-pill px-3 py-2 mb-3">{{ $categoryTitle }}</span>
                    <h1 class="display-6 fw-bold lh-base mb-3">{{ $title }}</h1>
                    <p class="lead text-secondary mb-4">{{ $summary ?: 'توضیح کوتاهی برای این مکان گردشگری ثبت نشده است.' }}</p>
                    <div class="d-flex flex-wrap gap-2 text-secondary small">
                        <span class="badge text-bg-light border rounded-pill px-3 py-2">📅 {{ jalali_date($place->published_at) ?: jalali_date($place->created_at) ?: 'بدون تاریخ' }}</span>
                        @if($place->location || $place->address)
                            <span class="badge text-bg-light border rounded-pill px-3 py-2">📍 {{ $place->location ?: \Illuminate\Support\Str::limit($place->address, 55) }}</span>
                        @endif
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="ratio ratio-16x9 rounded-4 overflow-hidden shadow-sm bg-secondary-subtle">
                        <img class="w-100 h-100 object-fit-cover" src="{{ $featuredImage }}" alt="{{ $title }}" loading="lazy">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-8">
                    <article class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-4 p-lg-5">
                            <h2 class="h4 fw-bold mb-3">معرفی {{ $title }}</h2>
                            <div class="text-secondary lh-lg fs-6">
                                {!! $place->description ?: '<p class="mb-0">توضیحات کامل این مکان هنوز در پایگاه داده ثبت نشده است.</p>' !!}
                            </div>
                        </div>
                    </article>

                    <section class="card border-0 shadow-sm rounded-4 mb-4" aria-labelledby="visit-info-heading">
                        <div class="card-body p-4 p-lg-5">
                            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
                                <div>
                                    <p class="text-success fw-bold mb-1">اطلاعات بازدید</p>
                                    <h2 id="visit-info-heading" class="h4 fw-bold mb-0">دسترسی، هزینه و راه‌های ارتباطی</h2>
                                </div>
                                @if($mapLink)
                                    <a class="btn btn-success rounded-pill px-4" href="{{ $mapLink }}" target="_blank" rel="noopener">مشاهده نقشه</a>
                                @endif
                            </div>

                            <div class="row g-3">
                                @foreach($visitInfoCards as $info)
                                    <div class="col-md-6">
                                        <div class="card h-100 border-0 bg-light rounded-4">
                                            <div class="card-body p-4">
                                                <div class="d-flex align-items-start gap-3">
                                                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-white shadow-sm fs-4" style="width:48px;height:48px">{{ $info['icon'] }}</span>
                                                    <div class="flex-grow-1">
                                                        <h3 class="h6 fw-bold mb-2">{{ $info['title'] }}</h3>
                                                        <p class="text-secondary mb-3" dir="{{ $info['dir'] ?? 'rtl' }}">{{ $info['value'] }}</p>
                                                        @if($info['link'])
                                                            <a class="small fw-bold text-success text-decoration-none" href="{{ $info['link'] }}" @if(! str_starts_with($info['link'], 'tel:')) target="_blank" rel="noopener" @endif>{{ $info['linkLabel'] }}</a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            @if($isEmbeddableMap)
                                <div class="ratio ratio-16x9 rounded-4 overflow-hidden mt-4 border">
                                    <iframe src="{{ $mapUrl }}" title="نقشه {{ $title }}" loading="lazy" allowfullscreen referrerpolicy="no-referrer-when-downgrade"></iframe>
                                </div>
                            @endif
                        </div>
                    </section>

                    <section class="card border-0 shadow-sm rounded-4" aria-labelledby="gallery-heading">
                        <div class="card-body p-4 p-lg-5">
                            <div class="mb-4">
                                <p class="text-success fw-bold mb-1">گالری تصاویر</p>
                                <h2 id="gallery-heading" class="h4 fw-bold mb-0">تصاویر {{ $title }}</h2>
                            </div>

                            <div class="row g-3" data-gallery-group="tourism-place-{{ $place->id }}">
                                @foreach($galleryImages as $image)
                                    <div class="col-6 col-md-4">
                                        <button type="button" class="btn p-0 border-0 bg-transparent w-100 rounded-4 overflow-hidden shadow-sm" data-gallery-item="{{ $image['url'] }}" aria-label="مشاهده تصویر {{ $image['caption'] ?? $title }}">
                                            <span class="ratio ratio-1x1 d-block bg-secondary-subtle">
                                                <img class="w-100 h-100 object-fit-cover" src="{{ $image['url'] }}" alt="{{ $image['caption'] ?? $title }}" loading="lazy">
                                            </span>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </section>
                </div>

                <aside class="col-lg-4">
                    <div class="position-sticky" style="top: 110px;">
                        <div class="card border-0 shadow-sm rounded-4 mb-4">
                            <div class="card-body p-4">
                                <h2 class="h5 fw-bold mb-3">خلاصه اطلاعات</h2>
                                <ul class="list-unstyled d-grid gap-3 mb-0 text-secondary">
                                    <li class="d-flex justify-content-between gap-3"><span>دسته‌بندی</span><strong class="text-dark">{{ $categoryTitle }}</strong></li>
                                    <li class="d-flex justify-content-between gap-3"><span>محدوده</span><strong class="text-dark">{{ $place->location ?: 'گرگان' }}</strong></li>
                                    <li class="d-flex justify-content-between gap-3"><span>تعداد تصاویر</span><strong class="text-dark">{{ $galleryImages->count() }}</strong></li>
                                </ul>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm rounded-4">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                                    <h2 class="h5 fw-bold mb-0">مکان‌های مرتبط</h2>
                                    <a class="small text-success fw-bold text-decoration-none" href="{{ route('tourism.index') }}">همه مکان‌ها</a>
                                </div>

                                <div class="row g-3">
                                    @forelse($relatedPlaces as $related)
                                        @php($relatedImage = image_url($related->featured_image ?: ($related->image ?: null)))
                                        <div class="col-12">
                                            <a class="card h-100 border-0 bg-light rounded-4 text-decoration-none text-dark overflow-hidden" href="{{ route('tourism.show', $related->slug) }}">
                                                <div class="row g-0 align-items-stretch">
                                                    <div class="col-4">
                                                        <img class="w-100 h-100 object-fit-cover" src="{{ $relatedImage }}" alt="{{ $related->title }}" loading="lazy">
                                                    </div>
                                                    <div class="col-8">
                                                        <div class="card-body p-3">
                                                            <span class="badge bg-white border text-secondary mb-2">{{ $related->category?->title ?: 'گردشگری' }}</span>
                                                            <h3 class="h6 fw-bold lh-base mb-2">{{ $related->title }}</h3>
                                                            <p class="small text-secondary mb-0">{{ plain_text($related->short_description ?: $related->description, 80) ?: 'توضیحی ثبت نشده است.' }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    @empty
                                        <div class="col-12">
                                            <div class="alert alert-light border rounded-4 mb-0">مکان مرتبطی برای نمایش ثبت نشده است.</div>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </section>
</main>
@endsection

@section('after_footer')
<div class="lightbox">
    <button class="lightbox-close" aria-label="بستن">✕</button>
    <button class="lightbox-nav lightbox-prev" aria-label="قبلی">‹</button>
    <button class="lightbox-nav lightbox-next" aria-label="بعدی">›</button>
    <img class="lightbox-img" src="" alt="تصویر بزرگ"/>
    <div class="lightbox-counter"></div>
</div>
@endsection