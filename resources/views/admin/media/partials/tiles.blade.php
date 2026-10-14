@forelse($media as $item)
    <article class="ml3-tile" data-ml3-item="{{ $item->id }}">
        <button type="button" class="ml3-image-button" data-ml3-details aria-label="نمایش جزئیات {{ $item->title ?: $item->original_name }}">
            <img src="{{ $item->url }}" alt="{{ $item->alt_text ?: $item->title ?: $item->original_name }}" loading="lazy" decoding="async">
            <span class="ml3-item-name" title="{{ $item->title ?: $item->original_name }}">{{ $item->title ?: $item->original_name }}</span>
        </button>
        <div class="ml3-tile-controls">
            <label class="ml3-select-label">
                <input type="checkbox" data-ml3-select value="{{ $item->id }}" aria-label="انتخاب {{ $item->title ?: $item->original_name }}">
                <span>انتخاب</span>
            </label>
            @if($canDelete)
                <button type="button" class="ml3-trash" data-ml3-delete-one
                    aria-label="حذف دائمی {{ $item->title ?: $item->original_name }}" title="حذف دائمی">
                    <svg aria-hidden="true" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M4 7h16M10 4h4m-8 3 1 13h10l1-13M10 10v7m4-7v7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
            @endif
        </div>
        <template data-ml3-detail-template>
            <div class="ml3-detail-preview">
                <img src="{{ $item->url }}" alt="{{ $item->alt_text ?: $item->original_name }}">
                <dl>
                    <div><dt>نام فایل</dt><dd>{{ $item->original_name }}</dd></div>
                    <div><dt>ابعاد</dt><dd>{{ $item->width ?: '—' }} × {{ $item->height ?: '—' }}</dd></div>
                    <div><dt>حجم</dt><dd>{{ number_format(($item->size ?: 0) / 1024, 1) }} KB</dd></div>
                    <div><dt>آپلودکننده</dt><dd>{{ $item->uploader?->name ?: '—' }}</dd></div>
                </dl>
            </div>
            <p class="ml3-path" dir="ltr">{{ $item->path }}</p>
            @if($canEdit)
                <form method="POST" action="{{ route('admin.media.update', $item) }}" class="ml3-detail-edit">
                    @csrf @method('PUT')
                    <label>عنوان<input class="form-control" name="title" value="{{ $item->title }}"></label>
                    <label>متن جایگزین<input class="form-control" name="alt_text" value="{{ $item->alt_text }}"></label>
                    <label>کپشن<textarea class="form-control" name="caption" rows="2">{{ $item->caption }}</textarea></label>
                    <label>توضیحات<textarea class="form-control" name="description" rows="3">{{ $item->description }}</textarea></label>
                    <button type="submit" class="admin-primary-btn">ذخیره اطلاعات</button>
                </form>
            @endif
            <button type="button" class="admin-secondary-btn" data-ml3-copy="{{ $item->url }}">کپی آدرس تصویر</button>
        </template>
    </article>
@empty
    <div class="ml3-empty">رسانه‌ای مطابق فیلترهای انتخاب‌شده پیدا نشد.</div>
@endforelse
