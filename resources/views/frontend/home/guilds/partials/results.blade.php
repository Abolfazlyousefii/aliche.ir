<div class="guilds-directory-results" data-guilds-results aria-busy="false" tabindex="-1">
    <div class="guilds-directory-results-content">
        @if($unions->isNotEmpty())
            <div class="guilds-directory-grid">
                @foreach($unions as $union)
                    @include('frontend.guilds.partials.card', ['union' => $union])
                @endforeach
            </div>
            @include('frontend.guilds.partials.pagination')
        @else
            <section class="guilds-directory-empty" aria-labelledby="guilds-empty-title">
                <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M4 21h16M6 21V9h12v12M9 13h2M13 13h2M9 17h2M13 17h2M8 9V5h8v4"/></svg>
                <h2 id="guilds-empty-title">اتحادیه‌ای پیدا نشد</h2>
                <p>{{ $search !== '' ? 'نتیجه‌ای مطابق عبارت جستجو پیدا نشد.' : ($type !== '' ? 'در این گروه هنوز اتحادیه فعالی ثبت نشده است.' : 'اتحادیه فعالی با فیلتر انتخاب‌شده وجود ندارد.') }}</p>
                <a href="{{ route('guilds.index') }}">نمایش همه اتحادیه‌ها</a>
            </section>
        @endif
    </div>
    <p class="guilds-directory-feedback" data-guilds-feedback role="status" aria-live="polite" aria-atomic="true"></p>
</div>
