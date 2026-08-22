<div class="galleries-directory-results" data-galleries-results aria-busy="false" tabindex="-1">
    <div class="galleries-directory-results-content">
        @if($galleries->isNotEmpty())
            <div class="galleries-directory-grid">
                @foreach($galleries as $gallery)
                    @include('frontend.galleries.partials.card', ['coverUrl' => $covers[$gallery->id] ?? null])
                @endforeach
            </div>
            @include('frontend.galleries.partials.pagination')
        @else
            <section class="galleries-directory-empty" aria-labelledby="galleries-empty-title">
                <svg viewBox="0 0 48 48" aria-hidden="true"><rect x="7" y="10" width="34" height="28" rx="3"/><circle cx="17" cy="20" r="3"/><path d="m10 34 9-9 6 6 5-5 8 8"/></svg>
                <h2 id="galleries-empty-title">گالری‌ای پیدا نشد</h2>
                <p>گالری مطابق جستجو یا فیلتر انتخاب‌شده وجود ندارد.</p>
                <a href="{{ route('galleries.index') }}">نمایش همه گالری‌ها</a>
            </section>
        @endif
    </div>
    <p class="galleries-directory-feedback" data-galleries-feedback role="status" aria-live="polite" aria-atomic="true"></p>
</div>
