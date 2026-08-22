@php
    $imagePath = $union->logo ?: $union->cover_image;
    $imageUrl = filled($imagePath) ? image_url($imagePath, '') : null;
    $typeKey = $union->unionType?->slug ?: $union->union_type;
    $typeLabel = str_replace('اتحادیه‌های ', '', $union->union_type_label);
    $phone = $union->phone ?: $union->mobile;
    $normalizedPhone = $phone ? preg_replace('/[^0-9+]/', '', strtr($phone, [
        '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
        '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
        '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
        '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
    ])) : null;
    $description = plain_text($union->short_description ?: $union->description, 150);
@endphp

<article class="guild-directory-card" data-guild-card>
    <div class="guild-directory-card-header">
        <div class="guild-directory-logo {{ $imageUrl ? 'has-image' : 'has-fallback' }}" data-guild-logo-wrap>
            @if($imageUrl)<img src="{{ $imageUrl }}" alt="{{ $union->display_title }}" loading="lazy" data-guild-logo>@endif
            <span class="guild-directory-logo-fallback" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M4 21h16M6 21V9h12v12M9 13h2M13 13h2M9 17h2M13 17h2M8 9V5h8v4"/></svg>
            </span>
        </div>
        <div class="guild-directory-identity">
            @if(filled($typeLabel))<span class="guild-directory-type type-{{ $typeKey }}">{{ $typeLabel }}</span>@endif
            <h3 class="guild-directory-title">{{ $union->display_title }}</h3>
        </div>
    </div>

    <div class="guild-directory-body">
        @if($description !== '')<p class="guild-directory-description">{{ $description }}</p>@endif

        @if($union->manager_name || $phone)
            <div class="guild-directory-meta">
                @if($union->manager_name)
                    <div class="guild-directory-manager">
                        <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM5 21a7 7 0 0 1 14 0"/></svg>
                        <span><small>مدیر اتحادیه</small><strong>{{ $union->manager_name }}</strong></span>
                    </div>
                @endif
                @if($phone && $normalizedPhone)
                    <div class="guild-directory-phone">
                        <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M7 3H4a1 1 0 0 0-1 1c0 9.4 7.6 17 17 17a1 1 0 0 0 1-1v-3l-4-1-1 2c-4.2-1.8-8.2-5.8-10-10l2-1Z"/></svg>
                        <span><small>شماره تماس</small><a href="tel:{{ $normalizedPhone }}">{{ fa_number($phone) }}</a></span>
                    </div>
                @endif
            </div>
        @endif
    </div>

    <div class="guild-directory-actions">
        <a class="guild-directory-view" href="{{ route('guilds.show', $union->slug) }}">مشاهده اطلاعات</a>
        @if($union->complaint_enabled)
            <a class="guild-directory-complaint" href="{{ route('complaints.create', ['union' => $union->id]) }}">ثبت شکایت</a>
        @endif
    </div>
</article>
