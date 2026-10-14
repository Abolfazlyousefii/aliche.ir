@extends('admin.layouts.app')

@section('title', 'کتابخانه رسانه')

@section('content')
@php
    $canUpload = request()->user()->hasPermission('media.upload');
    $canDelete = request()->user()->hasPermission('media.delete');
    $canEdit = request()->user()->hasPermission('media.edit');
@endphp

<section class="ml3" data-media-library-v3
    data-delete-one-url="{{ route('admin.media.destroy', ['medium' => '__MEDIA_ID__']) }}"
    data-delete-many-url="{{ route('admin.media.bulk_destroy') }}"
    data-upload-url="{{ route('admin.media.store') }}"
    data-can-delete="{{ $canDelete ? '1' : '0' }}"
    data-can-upload="{{ $canUpload ? '1' : '0' }}">
    <header class="ml3-heading">
        <div>
            <p class="admin-eyebrow">Media Library</p>
            <h1>کتابخانه رسانه</h1>
            <p>مدیریت تصاویر، آپلود گروهی، و حذف دائمی امن از فضای سایت</p>
        </div>
        <div class="ml3-heading-stats" aria-label="تعداد تصاویر">
            <strong>{{ number_format($media->total()) }}</strong>
            <span>رسانه در نتایج فعلی</span>
        </div>
    </header>

    <div class="ml3-layout">
        @if($canUpload)
            <aside class="ml3-upload-card" aria-labelledby="ml3UploadTitle">
                <div class="ml3-panel-heading">
                    <span class="ml3-heading-icon" aria-hidden="true">↑</span>
                    <div>
                        <h2 id="ml3UploadTitle">بارگذاری تصاویر</h2>
                        <p>آپلود به‌صورت جداگانه برای هر فایل</p>
                    </div>
                </div>
                <form data-ml3-upload enctype="multipart/form-data">
                    @csrf
                    <input id="ml3UploadInput" name="files[]" type="file"
                        accept=".jpg,.jpeg,.png,.webp,.gif,.avif,image/jpeg,image/png,image/webp,image/gif,image/avif"
                        multiple>
                    <label for="ml3UploadInput" class="ml3-dropzone" data-ml3-dropzone>
                        <span aria-hidden="true">↥</span>
                        <strong>تصاویر را اینجا رها کنید</strong>
                        <small>یا برای انتخاب چند تصویر کلیک کنید</small>
                    </label>
                    <p class="ml3-upload-guidance">۶۰ تصویر یا بیشتر؛ هر تصویر حداکثر {{ number_format(config('media.max_upload_kilobytes', 5120) / 1024, 0) }} مگابایت</p>
                    <div class="ml3-upload-buttons">
                        <button type="submit" class="admin-primary-btn" data-ml3-upload-start>شروع آپلود</button>
                        <button type="button" class="admin-secondary-btn" data-ml3-upload-retry hidden>تلاش مجدد ناموفق‌ها</button>
                    </div>
                    <p data-ml3-upload-status role="status" aria-live="polite">فایلی انتخاب نشده است.</p>
                    <progress data-ml3-upload-progress max="100" value="0" hidden></progress>
                    <ul class="ml3-upload-errors" data-ml3-upload-errors></ul>
                    <a href="{{ route('admin.media.index', ['sort' => 'newest']) }}" data-ml3-upload-refresh hidden>نمایش تصاویر بارگذاری‌شده</a>
                </form>
                <div class="ml3-help">
                    <strong>حفظ تصاویر سالم</strong>
                    <p>آپلودهای موفق با خطای سایر فایل‌ها از بین نمی‌روند. فقط برای تصاویر ناموفق تلاش مجدد انجام می‌شود.</p>
                </div>
            </aside>
        @endif

        <div class="ml3-library">
            <form class="ml3-toolbar" method="GET" action="{{ route('admin.media.index') }}">
                <div class="ml3-toolbar-title">
                    <h2>تصاویر کتابخانه</h2>
                    <p>روی هر تصویر بزنید تا جزئیات و ویرایش را ببینید</p>
                </div>
                <div class="ml3-filters">
                    <label class="ml3-field">
                        <span>جستجو</span>
                        <input class="form-control" name="search" value="{{ $search }}" placeholder="نام، عنوان یا متن جایگزین">
                    </label>
                    <label class="ml3-field">
                        <span>نوع فایل</span>
                        <select class="form-control" name="mime">
                            <option value="">همه تصاویر و فایل‌ها</option>
                            <option value="image/" @selected(request('mime') === 'image/')>فقط تصاویر</option>
                        </select>
                    </label>
                    <label class="ml3-field">
                        <span>مرتب‌سازی</span>
                        <select class="form-control" name="sort">
                            <option value="newest" @selected(request('sort', 'newest') === 'newest')>جدیدترین</option>
                            <option value="oldest" @selected(request('sort') === 'oldest')>قدیمی‌ترین</option>
                            <option value="largest" @selected(request('sort') === 'largest')>بیشترین حجم</option>
                            <option value="smallest" @selected(request('sort') === 'smallest')>کمترین حجم</option>
                        </select>
                    </label>
                    <button class="admin-secondary-btn" type="submit">اعمال فیلتر</button>
                </div>
            </form>

            <div class="ml3-actions" aria-label="مدیریت گروهی تصاویر">
                <div class="ml3-action-start">
                    <span class="ml3-action-icon" aria-hidden="true">✓</span>
                    <strong data-ml3-count role="status" aria-live="polite">۰ تصویر انتخاب‌شده</strong>
                    <span class="ml3-action-hint">انتخاب‌ها در صفحه‌های مختلف حفظ می‌شوند</span>
                </div>
                <div class="ml3-action-buttons">
                    <button type="button" class="admin-secondary-btn" data-ml3-select-page>انتخاب همه این صفحه</button>
                    <button type="button" class="admin-secondary-btn" data-ml3-clear>پاک کردن انتخاب‌ها</button>
                    @if($canDelete)
                        <button type="button" class="ml3-danger-button" data-ml3-delete-selected disabled>حذف دائمی انتخاب‌شده‌ها</button>
                    @endif
                </div>
            </div>

            <div class="ml3-result-message" data-ml3-result role="status" aria-live="polite" hidden></div>

            <div class="ml3-grid" data-ml3-grid>
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
            </div>
            <div class="ml3-pagination">
                @include('admin.partials.pagination', ['paginator' => $media])
            </div>
        </div>
    </div>

    <div class="ml3-modal" data-ml3-detail-modal hidden>
        <div class="ml3-modal-backdrop" data-ml3-close></div>
        <section class="ml3-dialog" role="dialog" aria-modal="true" aria-labelledby="ml3DetailTitle">
            <header><h2 id="ml3DetailTitle">جزئیات رسانه</h2><button type="button" data-ml3-close aria-label="بستن">×</button></header>
            <div class="ml3-dialog-body" data-ml3-detail-body></div>
        </section>
    </div>

    @if($canDelete)
    <div class="ml3-modal" data-ml3-confirm-modal hidden>
        <div class="ml3-modal-backdrop"></div>
        <section class="ml3-dialog ml3-confirm-dialog" role="alertdialog" aria-modal="true" aria-labelledby="ml3ConfirmTitle" aria-describedby="ml3ConfirmDescription">
            <header><h2 id="ml3ConfirmTitle">تأیید حذف دائمی تصاویر</h2><button type="button" data-ml3-cancel-delete aria-label="انصراف">×</button></header>
            <div class="ml3-dialog-body">
                <p id="ml3ConfirmDescription" data-ml3-confirm-description></p>
                <ul class="ml3-confirm-names" data-ml3-confirm-names></ul>
                <p class="ml3-confirm-warning">این عملیات قابل بازگشت نیست. فایل‌ها و نسخه‌های کوچک‌تر از هاست حذف می‌شوند؛ تصاویر استفاده‌شده در محتوای سایت محافظت خواهند شد.</p>
                <div class="ml3-confirm-actions">
                    <button type="button" class="admin-secondary-btn" data-ml3-cancel-delete>انصراف</button>
                    <button type="button" class="ml3-danger-button" data-ml3-confirm-delete>بله، حذف دائمی</button>
                </div>
            </div>
        </section>
    </div>
    @endif
</section>
@endsection

@push('scripts')
<script src="{{ asset('assets/admin/js/media-library-v3.js') }}?v={{ filemtime(public_path('assets/admin/js/media-library-v3.js')) }}"></script>
@endpush
