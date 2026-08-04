@extends('frontend.layouts.app')

@section('title', 'گردشگری گرگان | اتاق اصناف مرکز استان گلستان')
@section('meta_description', 'معرفی جاذبه‌های گردشگری، تاریخی و بازارهای گرگان')

@section('content')
<section class="page-header page-header-alt page-header-tourism">
  <div class="site-container">
    <h1>گردشگری در گرگان</h1>
    <nav class="breadcrumb">
      <a href="{{ route('home') }}">خانه</a>
      <span>گردشگری گرگان</span>
    </nav>
  </div>
</section>

<section class="tourism-intro">
  <div class="site-container">
    <div class="tourism-intro-grid">
      <div class="tourism-intro-text">
        <h2>{{ $tourismSettings['tourism.intro_title'] ?? 'به شهر گرگان خوش آمدید' }}</h2>
        <p>{{ plain_text($tourismSettings['tourism.intro_text'] ?? 'گرگان با جاذبه‌های طبیعی، تاریخی و بازارهای متنوع، یکی از مقصدهای مهم گردشگری استان گلستان است.') }}</p>
        <p>{{ plain_text($tourismSettings['tourism.intro_subtext'] ?? 'اتاق اصناف مرکز استان گلستان با همراهی اتحادیه‌های صنفی، پشتیبان فعالان حوزه گردشگری و مسافران محترم است.') }}</p>
        <div class="tourism-stats">
          <div class="tourism-stat"><strong>{{ $places->count() }}</strong><span>جاذبه ثبت‌شده</span></div>
          <div class="tourism-stat"><strong>{{ $tourismNature->count() }}</strong><span>طبیعت‌گردی</span></div>
          <div class="tourism-stat"><strong>{{ $tourismShop->count() }}</strong><span>بازار و خرید</span></div>
        </div>
      </div>
      <div class="tourism-intro-img">
        <img src="{{ $places->first()?->home_image_url ?? asset('assets/img/asnaf-gorgan-default.jpg') }}" alt="گرگان" loading="lazy"/>
      </div>
    </div>
  </div>
</section>

<section class="tourism-attractions">
  <div class="site-container">
    <div class="section-heading section-heading-centered">
      <h2>جاذبه‌های گردشگری</h2>
      <p>با معروف‌ترین جاذبه‌های طبیعی، تاریخی و تفریحی گرگان آشنا شوید</p>
    </div>
    <div class="tabs" data-tab-group="tour-attractions" role="tablist">
      @foreach ($tourismPanels as $panel => $panelData)
        <button class="tab-pill {{ $loop->first ? 'active' : '' }}" data-tab-target="{{ $panel }}" type="button">{{ $panelData['label'] }}</button>
      @endforeach
    </div>
    <div class="tab-panels" data-tab-panels="tour-attractions">
      @foreach ($tourismPanels as $panel => $panelData)
        @php($panelPlaces = $panelData['items'])
        <div class="tab-panel {{ $loop->first ? 'active' : '' }}" data-tab-panel="{{ $panel }}">
          <div class="tourism-grid tourism-grid-lg">
            @forelse ($panelPlaces as $place)
              <div class="tourism-card tourism-card-lg">
                <a href="{{ route('tourism.show', $place->slug) }}">
                  <div class="tourism-img-wrap">
                    <img src="{{ $place->home_image_url }}" alt="{{ $place->title }}" loading="lazy"/>
                    <div class="tourism-badge">{{ $place->home_badge }}</div>
                  </div>
                </a>
                <div class="tourism-card-body">
                  <h3>{{ $place->title }}</h3>
                  <p>{{ plain_text($place->home_description, 150) ?: 'توضیحی برای این جاذبه ثبت نشده است.' }}</p>
                  <div class="tourism-card-footer"><span>{{ $place->home_badge }}</span></div>
                </div>
              </div>
            @empty
              <div class="tourism-card tourism-card-lg">
                <a href="{{ route('tourism.index') }}">
                  <div class="tourism-img-wrap">
                    <img src="{{ asset('assets/img/asnaf-gorgan-default.jpg') }}" alt="موردی موجود نیست" loading="lazy"/>
                    <div class="tourism-badge">گردشگری</div>
                  </div>
                </a>
                <div class="tourism-card-body">
                  <h3>موردی موجود نیست</h3>
                  <p>در حال حاضر آیتمی برای این دسته گردشگری ثبت نشده است.</p>
                  <div class="tourism-card-footer"><span>گردشگری</span></div>
                </div>
              </div>
            @endforelse
          </div>
        </div>
      @endforeach
    </div>
  </div>
</section>

<section class="tourism-gallery">
  <div class="site-container">
    <div class="section-heading section-heading-centered">
      <h2>گالری تصاویر گردشگری</h2>
      <p>تصاویری از زیبایی‌های طبیعی و تاریخی گرگان</p>
    </div>
    <div class="tourism-gallery-grid" data-gallery-group="tourism-gallery">
      @forelse ($galleryPlaces as $place)
        <div class="tourism-gallery-item" data-gallery-item="{{ $place->home_image_url }}"><img src="{{ $place->home_image_url }}" alt="{{ $place->title }}" loading="lazy"/></div>
      @empty
        <div class="tourism-gallery-item" data-gallery-item="{{ asset('assets/img/asnaf-gorgan-default.jpg') }}"><img src="{{ asset('assets/img/asnaf-gorgan-default.jpg') }}" alt="موردی موجود نیست" loading="lazy"/></div>
      @endforelse
    </div>
  </div>
</section>

<section class="tourism-cta">
  <div class="site-container">
    <div class="tourism-cta-box">
      <h2>اصناف مرتبط با گردشگری</h2>
      <p>اتاق اصناف مرکز استان گلستان با اتحادیه‌های هتل‌داران، رستوران‌داران، صنایع دستی و آژانس‌های مسافرتی در خدمت فعالان این حوزه است.</p>
      <a href="{{ route('guilds.index') }}" class="cta-button">مشاهده اتحادیه‌های صنفی</a>
    </div>
  </div>
</section>
@endsection
