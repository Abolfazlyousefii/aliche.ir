@props(['name'])

@if(in_array($name, ['bale', 'telegram', 'instagram'], true))
    <span {{ $attributes->merge(['class' => 'social-brand-icon']) }} aria-hidden="true" data-brand-icon="{{ $name }}">@include('components.social-brands.'.$name)</span>
@else
    <x-ui-icon :name="$name" {{ $attributes }} style="fill:none;stroke:currentColor;stroke-width:1.7" />
@endif
