<section class="commissions-section ds-tint-block" id="commissions">
    <div class="site-container">
        <div class="section-heading"><h2>{{ $section->title }}</h2><a class="tab-pill" href="{{ route('commissions.index') }}">مشاهده همه کمیسیون‌ها</a></div>
        <div class="commission-card"><div class="commission-grid compact-grid">
            @forelse ($commissions ?? collect() as $index => $commission)
                <a class="commission-item {{ $index % 2 ? 'green' : 'blue' }}" href="{{ route('commissions.show', $commission->slug) }}"><strong>{{ $commission->title }}</strong><span>{{ plain_text($commission->description, 90) ?: $commission->sessions_count.' جلسه منتشرشده' }}</span></a>
            @empty
                <a class="commission-item blue" href="{{ route('commissions.index') }}"><strong>موردی موجود نیست</strong><span>در حال حاضر کمیسیونی برای نمایش منتشر نشده است.</span></a>
            @endforelse
        </div></div>
    </div>
</section>
