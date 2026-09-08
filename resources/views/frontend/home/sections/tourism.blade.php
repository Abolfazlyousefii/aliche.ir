<section class="tourism-section" id="tourism">
    <div class="site-container">
        <div class="section-heading">
            <h2>{{ $section->title }}</h2>
            <a class="tab-pill" href="{{ route('tourism.index') }}">مشاهده همه</a>
        </div>
        <div aria-label="گردشگری" class="tabs" data-tab-group="tourism" role="tablist">
            @foreach ($tourismPanels as $panelId => $panel)
                <button class="tab-pill {{ $loop->first ? 'active' : '' }}" data-tab-target="{{ $panelId }}" type="button" role="tab" aria-selected="{{ $loop->first ? 'true' : 'false' }}">{{ $panel['label'] }}</button>
            @endforeach
        </div>
        <div data-tab-panels="tourism">
        @foreach ($tourismPanels as $panelId => $panel)
            <div class="tab-panel {{ $loop->first ? 'active' : '' }}" data-tab-panel="{{ $panelId }}" role="tabpanel">
                <div class="tourism-grid">
                    @foreach ($panel['items'] as $place)
                        <div class="tourism-card">
                            <a href="{{ route('tourism.show', $place->slug) }}">
                                <div class="tourism-img-wrap">
                                    <img src="{{ $place->home_image_url }}" alt="{{ $place->title }}" loading="lazy" decoding="async"/>
                                    <div class="tourism-badge">{{ $place->home_badge }}</div>
                                </div>
                                <div class="tourism-card-body">
                                    <h3>{{ $place->title }}</h3>
                                    <p>{{ plain_text($place->home_description, 120) ?: 'توضیحی برای این جاذبه ثبت نشده است.' }}</p>
                                    <div class="tourism-card-footer"><span>{{ $place->home_location ?: 'موقعیت ثبت نشده است' }}</span></div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
        </div>
    </div>
</section>
