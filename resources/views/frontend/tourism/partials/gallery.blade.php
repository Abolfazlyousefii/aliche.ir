<section class="tourism-directory-gallery" aria-labelledby="tourism-gallery-title">
  <div class="site-container">
    <div class="tourism-directory-section-heading"><div><span>قاب‌هایی از گرگان</span><h2 id="tourism-gallery-title">گالری تصاویر گردشگری</h2></div></div>
    @if(count($galleryItems))
      <div class="tourism-gallery-grid" data-gallery-group="tourism-gallery">
        @foreach($galleryItems as $item)
          <button type="button" class="tourism-gallery-item" data-gallery-item="{{ $item['url'] }}" aria-label="مشاهده تصویر {{ $item['caption'] }}">
            <img src="{{ $item['url'] }}" alt="{{ $item['caption'] }}" loading="lazy" decoding="async">
            <span>{{ $item['caption'] }}</span>
            <svg aria-hidden="true" viewBox="0 0 24 24"><circle cx="11" cy="11" r="6"/><path d="m16 16 4 4M11 8v6M8 11h6"/></svg>
          </button>
        @endforeach
      </div>
    @else
      <p class="tourism-gallery-empty">هنوز تصویر معتبری برای گالری گردشگری ثبت نشده است.</p>
    @endif
  </div>
</section>
