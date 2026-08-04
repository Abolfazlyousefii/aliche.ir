<section class="representatives-section section-white" id="representatives">
    <div class="site-container">
        <div class="section-heading"><h2>{{ $section->title }}</h2><a class="tab-pill" href="{{ route('guilds.index') }}">فهرست اتحادیه‌ها</a></div>
        @php
            $panels = ($unionTypeTabs ?? collect())->mapWithKeys(fn ($data, $slug) => ['rep-'.$slug => $data]);
            if ($panels->isEmpty()) {
                $panels = collect([
                    'rep-production' => ['label' => 'اتحادیه‌های تولیدی', 'icon' => '', 'items' => $productionUnions ?? collect()],
                    'rep-distribution' => ['label' => 'اتحادیه‌های توزیعی', 'icon' => '', 'items' => $distributionUnions ?? collect()],
                    'rep-service' => ['label' => 'اتحادیه‌های خدماتی', 'icon' => '', 'items' => $serviceUnions ?? collect()],
                ]);
            }
        @endphp
        <div aria-label="نماینده‌ها" class="tabs" data-tab-group="representatives" role="tablist">
            @foreach($panels as $panel => $data)
                <button class="tab-pill {{ $loop->first ? 'active' : '' }}" data-tab-target="{{ $panel }}" type="button">{{ trim(($data['icon'] ?? '').' '.$data['label']) }}</button>
            @endforeach
        </div>
        <div class="tab-panels" data-tab-panels="representatives">
            @foreach($panels as $panel => $data)
                <div class="tab-panel {{ $loop->first ? 'active' : '' }}" data-tab-panel="{{ $panel }}">
                    <div class="representative-layout">
                        <div class="representative-map">
                            <button class="soft-button map-badge">{{ $data['label'] }} استان گلستان</button>
                            <img alt="{{ $data['label'] }}" class="map-img" src="{{ asset('assets/img/asnaf-gorgan-default.jpg') }}">
                        </div>
                        <aside class="people-panel" data-search-area>
                            <div class="searchbox"><span class="search-icon"></span><input data-filter-input data-union-search-url="{{ route('guilds.search') }}" data-union-type="{{ str_replace('rep-', '', $panel) }}" placeholder="جستجوی سریع اتحادیه..." type="search"></div>
                            <div class="people-scroll-wrap"><ul class="person-list" data-union-results>
                                @forelse(($data['items'] ?? collect()) as $union)
                                    <li><a href="{{ route('guilds.show', $union->slug) }}" class="d-flex align-items-center gap-2 text-decoration-none"><span class="person-avatar avatar-{{ ($loop->iteration % 6) + 1 }}">@if($union->logo || $union->cover_image)<img src="{{ image_url($union->logo ?: $union->cover_image) }}" alt="{{ $union->display_title }}" loading="lazy">@endif</span><div><strong>{{ $union->display_title }}</strong><small>{{ plain_text($union->short_description ?: $union->manager_name ?: $union->union_type_label, 90) }}</small></div></a></li>
                                @empty
                                    <li><span class="person-avatar avatar-1"></span><div><strong>اتحادیه‌ای ثبت نشده است</strong><small>به‌زودی اطلاعات اتحادیه‌ها نمایش داده می‌شود.</small></div></li>
                                @endforelse
                            </ul></div>
                        </aside>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
