@php
    $defaultAdImage = asset('assets/img/asnaf-gorgan-default.jpg');
    $advertisementItems = collect($homeAdvertisements ?? collect())
        ->take(4)
        ->filter(fn ($advertisement) => filled($advertisement->image_url ?? null))
        ->map(fn ($advertisement) => [
            'title' => $advertisement->title ?: 'تبلیغات',
            'url' => filled($advertisement->link) && $advertisement->link !== '#' ? $advertisement->link : route('contact.create'),
            'target' => $advertisement->target ?: '_self',
            'image' => $advertisement->image_url ?: $defaultAdImage,
            'placeholder' => false,
        ])
        ->values();

    while ($advertisementItems->count() < 4) {
        $advertisementItems->push([
            'title' => 'جای تبلیغ شما',
            'url' => route('contact.create'),
            'target' => '_self',
            'image' => $defaultAdImage,
            'placeholder' => true,
        ]);
    }
@endphp

<section class="home-ad-banners site-container mid-ad">
    @foreach ($advertisementItems as $advertisement)
        <a class="ad-banner {{ $advertisement['placeholder'] ? 'ad-banner-placeholder' : '' }}" href="{{ $advertisement['url'] }}" target="{{ $advertisement['target'] }}" @if($advertisement['target'] === '_blank') rel="noopener" @endif>
            <img alt="{{ $advertisement['title'] }}" src="{{ $advertisement['image'] }}" loading="lazy"/>
            <div class="ad-banner-overlay"></div>
            <div class="ad-banner-text">{{ $advertisement['title'] }}</div>
        </a>
    @endforeach
</section>
