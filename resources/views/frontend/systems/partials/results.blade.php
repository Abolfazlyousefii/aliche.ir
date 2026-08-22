<div class="systems-directory-results" data-systems-results aria-busy="false" tabindex="-1">
    <div class="systems-directory-results-content">
        @if($systems->isNotEmpty())
            <div class="systems-directory-grid">
                @foreach($systems as $system)
                    @include('frontend.systems.partials.card', ['entryLink' => $entryLinks[$system->id] ?? null])
                @endforeach
            </div>
            @include('frontend.systems.partials.pagination')
        @else
            <section class="systems-directory-empty" aria-labelledby="systems-empty-title">
                <svg viewBox="0 0 48 48" aria-hidden="true"><path d="M9 13h30v24H9zM15 19h7v6h-7zm11 0h7v6h-7zM15 30h18M20 37v-7m8 7v-7"/></svg>
                <h2 id="systems-empty-title">سامانه‌ای پیدا نشد</h2>
                <p>سامانه‌ای مطابق جستجو یا دسته‌بندی انتخاب‌شده وجود ندارد.</p>
                <a href="{{ route('systems.index') }}">نمایش همه سامانه‌ها</a>
            </section>
        @endif
    </div>
    <p class="systems-directory-feedback" data-systems-feedback role="status" aria-live="polite" aria-atomic="true"></p>
</div>
