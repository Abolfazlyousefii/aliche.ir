<nav class="tourism-type-tabs" aria-label="فیلتر نوع جاذبه">
  <a class="tourism-type-tab {{ $activeType === null ? 'is-active' : '' }}" href="{{ route('tourism.index') }}#tourism-attractions" data-tourism-type-link data-tourism-type="" @if($activeType === null) aria-current="page" @endif>
    <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M4 5h6v6H4zM14 5h6v6h-6zM4 15h6v4H4zM14 15h6v4h-6z"/></svg>
    <span>همه جاذبه‌ها</span><b>{{ $typeCounts['all'] }}</b>
  </a>
  @foreach($types as $type => $label)
    <a class="tourism-type-tab {{ $activeType === $type ? 'is-active' : '' }}" href="{{ route('tourism.index', ['type' => $type]) }}#tourism-attractions" data-tourism-type-link data-tourism-type="{{ $type }}" @if($activeType === $type) aria-current="page" @endif>
      <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M12 3v18M5 12l7-9 7 9M4 21h16"/></svg>
      <span>{{ $label }}</span><b>{{ $typeCounts[$type] }}</b>
    </a>
  @endforeach
</nav>
