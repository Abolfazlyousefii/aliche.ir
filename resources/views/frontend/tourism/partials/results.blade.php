<div class="tourism-directory-results" data-tourism-results aria-live="polite" aria-busy="false">
  @if($places->isNotEmpty())
    <div class="tourism-directory-grid">
      @foreach($places as $place)
        @include('frontend.tourism.partials.card', ['place' => $place])
      @endforeach
    </div>
  @else
    <div class="tourism-directory-empty">
      <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M4 19V7l4-3 4 3 4-3 4 3v12l-4 3-4-3-4 3-4-3Zm4-15v15m4-12v12m4-15v15"/></svg>
      <h3>جاذبه‌ای پیدا نشد</h3>
      <p>در این دسته هنوز جاذبه فعالی ثبت نشده است.</p>
      <a href="{{ route('tourism.index') }}" data-tourism-type-link data-tourism-type="">نمایش همه جاذبه‌ها</a>
    </div>
  @endif
</div>
