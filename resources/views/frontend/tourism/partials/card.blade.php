@php
  $imageUrl = $place->directory_image_url ?: asset('assets/img/tourism-placeholder.svg');
  $typeLabel = $types[$place->tourism_type] ?? 'سایر';
  $description = plain_text($place->short_description ?: $place->description, 170);
  $metaItems = collect([
    filled($place->address) ? ['kind' => 'address', 'label' => plain_text($place->address, 75)] : null,
    filled($place->working_hours) ? ['kind' => 'hours', 'label' => plain_text($place->working_hours, 55)] : null,
    filled($place->visit_price) ? ['kind' => 'price', 'label' => plain_text($place->visit_price, 45)] : null,
  ])->filter()->take(2);
@endphp
<article class="tourism-directory-card">
  <a class="tourism-directory-media" href="{{ route('tourism.show', $place->slug) }}">
    <img src="{{ $imageUrl }}" alt="{{ $place->title }}" loading="lazy" decoding="async">
    <span class="tourism-directory-badge">{{ $typeLabel }}</span>
  </a>
  <div class="tourism-directory-body">
    <h3 class="tourism-directory-title"><a href="{{ route('tourism.show', $place->slug) }}">{{ $place->title }}</a></h3>
    @if($description)<p class="tourism-directory-description">{{ $description }}</p>@endif
    @if($metaItems->isNotEmpty())
      <ul class="tourism-directory-meta">
        @foreach($metaItems as $meta)
          <li><svg aria-hidden="true" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg><span>{{ $meta['label'] }}</span></li>
        @endforeach
      </ul>
    @endif
    <a class="tourism-directory-action" href="{{ route('tourism.show', $place->slug) }}">مشاهده راهنما <svg aria-hidden="true" viewBox="0 0 24 24"><path d="m15 6-6 6 6 6"/></svg></a>
  </div>
</article>
