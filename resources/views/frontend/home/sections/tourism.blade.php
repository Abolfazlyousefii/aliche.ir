<section class="tourism-section" id="tourism">
    <div class="site-container">
        <div class="section-heading">
            <h2>{{ $section->title }}</h2>
            <a class="tab-pill" href="{{ route('tourism.index') }}">مشاهده همه</a>
        </div>
        <div aria-label="گردشگری" class="tabs" data-tab-group="tourism" role="tablist">
            <button class="tab-pill active" data-tab-target="tourism-nature" type="button">طبیعت‌گردی</button>
            <button class="tab-pill" data-tab-target="tourism-historic" type="button">تاریخی</button>
            <button class="tab-pill" data-tab-target="tourism-shop" type="button">بازار و خرید</button>
        </div>
        <div data-tab-panels="tourism">
        @foreach ([
            'tourism-nature' => $tourismNature ?? collect(),
            'tourism-historic' => $tourismHistoric ?? collect(),
            'tourism-shop' => $tourismShop ?? collect(),
        ] as $panel => $places)
            <div class="tab-panel {{ $loop->first ? 'active' : '' }}" data-tab-panel="{{ $panel }}">
                <div class="tourism-grid">
                    @forelse ($places as $place)
                        <div class="tourism-card">
                            <a href="{{ route('tourism.show', $place->slug) }}">
                                <div class="tourism-img-wrap">
                                    <img src="{{ $place->home_image_url }}" alt="{{ $place->title }}" loading="lazy"/>
                                    <div class="tourism-badge">{{ $place->home_badge }}</div>
                                </div>
                                <div class="tourism-card-body">
                                    <h3>{{ $place->title }}</h3>
                                    <p>{{ plain_text($place->home_description, 120) ?: 'توضیحی برای این جاذبه ثبت نشده است.' }}</p>
                                    <div class="tourism-card-footer"><span>{{ $place->home_location ?: 'موقعیت ثبت نشده است' }}</span></div>
                                </div>
                            </a>
                        </div>
                    @empty
                        <div class="tourism-card">
                            <div class="tourism-img-wrap">
                                <img src="{{ asset('assets/img/asnaf-gorgan-default.jpg') }}" alt="موردی موجود نیست" loading="lazy"/>
                                <div class="tourism-badge">گردشگری</div>
                            </div>
                            <div class="tourism-card-body">
                                <h3>موردی موجود نیست</h3>
                                <p>در حال حاضر آیتمی برای این دسته گردشگری ثبت نشده است.</p>
                                <div class="tourism-card-footer"><span>اتاق اصناف مرکز استان گلستان</span></div>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        @endforeach
        </div>
    </div>
</section>
