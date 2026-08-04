@php($hasChildren = $menuItem->children->isNotEmpty())
<li class="{{ $itemClass }} {{ $hasChildren ? 'has-top-submenu top-nav-item' : '' }}">
    @if ($hasChildren && $variant !== 'compact')
        <button aria-expanded="false" class="{{ $linkClass }} top-nav-link" type="button">{{ $menuItem->icon }} {{ $menuItem->title }}<span class="top-submenu-caret"></span></button>
        <ul class="top-submenu">
            @foreach ($menuItem->children as $child)
                <li><a href="{{ $child->resolved_url }}" target="{{ $child->target }}">{{ $child->icon }} {{ $child->title }}</a></li>
            @endforeach
        </ul>
    @elseif ($hasChildren)
        <button class="{{ $linkClass }}" aria-expanded="false">{{ $menuItem->icon }} {{ $menuItem->title }}</button>
        <div class="top-submenu">
            @foreach ($menuItem->children as $child)
                <a href="{{ $child->resolved_url }}" target="{{ $child->target }}">{{ $child->icon }} {{ $child->title }}</a>
            @endforeach
        </div>
    @else
        <a class="{{ $linkClass }}{{ request()->url() === $menuItem->resolved_url ? ' active' : '' }}" href="{{ $menuItem->resolved_url }}" target="{{ $menuItem->target }}">{{ $menuItem->icon }} {{ $menuItem->title }}</a>
    @endif
</li>
