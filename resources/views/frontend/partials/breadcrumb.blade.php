@if (! empty($items))
<nav class="breadcrumb">
@foreach ($items as $item)
    @if (! empty($item['url']))
        <a href="{{ $item['url'] }}">{{ $item['title'] }}</a>
    @else
        <span>{{ $item['title'] }}</span>
    @endif
@endforeach
</nav>
@endif
