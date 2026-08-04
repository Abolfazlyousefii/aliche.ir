@php
    $marketTypes = ['gold', 'coin', 'silver', 'currency'];
    $marketItems = \App\Models\Price::query()
        ->active()
        ->whereIn('type', $marketTypes)
        ->whereNotNull('amount')
        ->where(function ($query) {
            $query->where('title', 'like', '%طلا%')
                ->orWhere('title', 'like', '%سکه%')
                ->orWhere('title', 'like', '%نقره%')
                ->orWhere('title', 'like', '%دلار%')
                ->orWhere('title', 'like', '%یورو%');
        })
        ->orderByRaw("CASE WHEN title LIKE '%طلا%' THEN 1 WHEN title LIKE '%سکه%' THEN 2 WHEN title LIKE '%نقره%' THEN 3 WHEN title LIKE '%دلار%' THEN 4 WHEN title LIKE '%یورو%' THEN 5 ELSE 9 END")
        ->orderBy('sort_order')
        ->orderBy('title')
        ->take(8)
        ->get()
        ->map(fn ($item) => [
            'title' => $item->title,
            'value' => fa_number($item->amount),
            'unit' => $item->unit,
            'meta' => trim(($item->source ? $item->source.' · ' : '').'هر ۱ ساعت آپدیت می‌شود'.(($item->fetched_at ?: $item->published_at ?: $item->updated_at) ? ' · آخرین بروزرسانی: '.jalali_datetime($item->fetched_at ?: $item->published_at ?: $item->updated_at) : '')),
        ]);

    if ($marketItems->isEmpty()) {
        $settings = app(\App\Services\SettingService::class);
        $fallbackKeys = ['prices.gold', 'prices.coin', 'prices.silver', 'prices.usd'];
        $marketItems = collect($fallbackKeys)
            ->map(fn ($key) => $settings->get($key))
            ->filter(fn ($item) => is_array($item) && filled($item['label'] ?? null) && filled($item['value'] ?? null) && ($item['value'] ?? '—') !== '—')
            ->map(fn ($item) => [
                'title' => $item['label'],
                'value' => fa_number($item['value']),
                'unit' => $item['unit'] ?? 'تومان',
                'meta' => $item['trend'] ?? 'به‌روزرسانی از تنظیمات سایت',
            ])
            ->values();
    }
@endphp
@if($marketItems->isNotEmpty())
<div class="market-ticker-wrap" aria-label="قیمت روز طلا و ارز">
    <div class="site-container market-ticker" data-market-ticker>
        <div class="market-ticker-title">قیمت روز طلا و ارز</div>
        <div class="market-ticker-items">
            @foreach($marketItems as $item)
                <div class="market-ticker-item {{ $loop->first ? 'is-active' : '' }}" data-market-item>
                    <strong>{{ $item['title'] }}</strong>
                    <span class="market-price">{{ $item['value'] }} {{ $item['unit'] }}</span>
                    <small>{{ $item['meta'] }}</small>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endif
