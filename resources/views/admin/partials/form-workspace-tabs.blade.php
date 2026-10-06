<div class="admin-workspace-intro">
    <div>
        <span class="admin-workspace-eyebrow">{{ $workspaceEyebrow }}</span>
        <h3>{{ $workspaceHeading }}</h3>
        <p>{{ $workspaceDescription }}</p>
    </div>
    <span class="admin-workspace-intro__count">{{ fa_number(count($workspaceTabs)) }} بخش</span>
</div>

@if($errors->any())
    <div class="admin-workspace-errors" role="alert" aria-label="خطاهای فرم">
        <strong>برخی اطلاعات نیاز به اصلاح دارند</strong>
        <p>موارد زیر را بررسی کنید. انتخاب هر خطا، بخش مربوط را باز می‌کند.</p>
        <ul>
            @foreach($errors->messages() as $field => $messages)
                @foreach($messages as $message)
                    <li><button type="button" data-admin-workspace-error="{{ $field }}">{{ $message }}</button></li>
                @endforeach
            @endforeach
        </ul>
    </div>
@endif

<div class="admin-workspace-nav-wrap">
    <div class="admin-workspace-nav" role="tablist" aria-label="{{ $workspaceHeading }}">
        @foreach($workspaceTabs as $index => $tab)
            <button
                class="admin-workspace-tab {{ $index === 0 ? 'is-active' : '' }}"
                type="button"
                role="tab"
                id="admin-form-tab-{{ $tab['id'] }}"
                data-admin-workspace-tab="{{ $tab['id'] }}"
                aria-controls="admin-form-pane-{{ $tab['id'] }}"
                aria-selected="{{ $index === 0 ? 'true' : 'false' }}"
                tabindex="{{ $index === 0 ? '0' : '-1' }}"
            ><span class="admin-workspace-tab__number">{{ fa_number($index + 1) }}</span><span>{{ $tab['title'] }}</span></button>
        @endforeach
    </div>
    <p class="admin-workspace-nav-hint">بخش‌ها فقط برای ویرایش جدا شده‌اند؛ با یک بار ذخیره، همه اطلاعات ثبت می‌شوند.</p>
</div>
