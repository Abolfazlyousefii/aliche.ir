@php
    $selectedIcon = old('icon', $system->icon ?? '💻');
@endphp
<div class="admin-icon-picker" data-icon-picker>
    <input class="form-control" id="icon" name="icon" value="{{ $selectedIcon }}" placeholder="مثلا 💻 یا نام کلاس آیکن" data-icon-picker-input>
    <div class="admin-icon-presets" aria-label="آیکن‌های آماده سامانه‌ها">
        @foreach ($iconPresets as $icon => $label)
            <button type="button" class="admin-icon-preset {{ $selectedIcon === $icon ? 'is-selected' : '' }}" data-icon-value="{{ $icon }}" title="{{ $label }}" aria-label="{{ $label }}">
                <span>{{ $icon }}</span>
                <small>{{ $label }}</small>
            </button>
        @endforeach
    </div>
    <small class="text-muted">از آیکن‌های آماده انتخاب کنید یا ایموجی/کلاس دلخواه وارد کنید.</small>
</div>
