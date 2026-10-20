@php
    $boardUser = auth()->user();
    $canViewBoard = $boardUser?->hasPermission('union_members.view') ?? false;
    $canAddBoard = $boardUser?->hasPermission('union_members.create') ?? false;
    $canEditBoard = $boardUser?->hasPermission('union_members.edit') ?? false;
    $canDeleteBoard = $boardUser?->hasPermission('union_members.delete') ?? false;
@endphp

@if($canViewBoard || $canAddBoard || $canEditBoard)
<section class="admin-panel-card mt-4" id="union-board-management" aria-labelledby="union-board-title">
    <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap mb-3">
        <div>
            <h3 class="h5 mb-1" id="union-board-title">هیئت‌مدیرهٔ این اتحادیه</h3>
            <p class="small text-muted mb-0">این فهرست فقط به {{ $union->display_title }} تعلق دارد؛ ثبت عضو مستقل از ذخیره اطلاعات اصلی اتحادیه است.</p>
            <p class="small text-muted mb-0">نمایش در سایت با گزینهٔ «نمایش اعضای هیئت مدیره» در تنظیمات صفحه اتحادیه کنترل می‌شود.</p>
        </div>
        <span class="text-muted small">{{ fa_number($union->members->count()) }} عضو ثبت‌شده</span>
    </div>

    @if($canViewBoard || $canEditBoard)
        @forelse($union->members as $member)
            <details class="border-bottom py-3" @if(old('board_member_id') == $member->id) open @endif>
                <summary class="d-flex align-items-center gap-3" style="cursor:pointer;list-style:auto">
                    @if($member->image)
                        <img src="{{ $member->image_url }}" width="50" height="62" style="object-fit:cover;object-position:center 20%" alt="">
                    @else
                        <span class="bg-light border d-inline-flex align-items-center justify-content-center text-muted small" style="width:50px;height:62px">بدون عکس</span>
                    @endif
                    <span class="flex-grow-1">
                        <strong>{{ $member->full_name }}</strong>
                        <small class="text-muted d-block">{{ $member->position ?: 'بدون سمت' }} · ترتیب {{ fa_number($member->sort_order) }} · {{ $member->is_active && $member->status === 'active' ? 'فعال' : 'غیرفعال' }}</small>
                    </span>
                    @if($canEditBoard)<span class="small text-primary">ویرایش</span>@endif
                </summary>
                @if($canEditBoard)
                    <form class="row g-3 mt-2" action="{{ route('admin.union_members.update', $member) }}" method="post" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="union_id" value="{{ $union->id }}">
                        <input type="hidden" name="board_editor" value="1">
                        <div class="col-md-4">
                            <label class="form-label" for="board_first_{{ $member->id }}">نام</label>
                            <input class="form-control" id="board_first_{{ $member->id }}" name="first_name" maxlength="90" value="{{ $member->first_name }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="board_last_{{ $member->id }}">نام خانوادگی</label>
                            <input class="form-control" id="board_last_{{ $member->id }}" name="last_name" maxlength="90" value="{{ $member->last_name }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="board_full_{{ $member->id }}">نام کامل فعلی (اطلاعات قدیمی)</label>
                            <input class="form-control" id="board_full_{{ $member->id }}" name="full_name" maxlength="190" required value="{{ $member->full_name }}">
                            <small class="text-muted">در صورت واردکردن نام و نام خانوادگی، نام کامل خودکار به‌روزرسانی می‌شود.</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="board_position_{{ $member->id }}">سمت</label>
                            <input class="form-control" id="board_position_{{ $member->id }}" name="position" maxlength="190" value="{{ $member->position }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label" for="board_sort_{{ $member->id }}">ترتیب</label>
                            <input class="form-control" id="board_sort_{{ $member->id }}" type="number" min="0" name="sort_order" value="{{ $member->sort_order }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label" for="board_active_{{ $member->id }}">نمایش</label>
                            <select class="form-control" id="board_active_{{ $member->id }}" name="is_active">
                                <option value="1" @selected($member->is_active)>فعال</option>
                                <option value="0" @selected(!$member->is_active)>غیرفعال</option>
                            </select>
                            <input type="hidden" name="status" value="{{ $member->status }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="board_image_{{ $member->id }}">تعویض عکس</label>
                            <input class="form-control" id="board_image_{{ $member->id }}" type="file" accept="image/*" name="image" data-skip-media-picker>
                            <button class="admin-secondary-btn mt-2" type="button" data-media-select-target="board_media_{{ $member->id }}">انتخاب از کتابخانه</button>
                            <select class="d-none" id="board_media_{{ $member->id }}" name="image_media_id" tabindex="-1" aria-hidden="true">
                                <option value="">بدون تغییر</option>
                                @foreach(($mediaItems ?? collect()) as $media)
                                    <option value="{{ $media->id }}" data-url="{{ $media->url }}">{{ $media->title ?: $media->original_name }}</option>
                                @endforeach
                            </select>
                            @if($member->image)
                                <label class="small d-block mt-2"><input type="checkbox" name="remove_image" value="1"> حذف عکس فعلی</label>
                            @endif
                        </div>
                        <div class="col-12"><button class="admin-primary-btn" type="submit">ذخیره تغییرات عضو</button></div>
                    </form>
                    @if($canDeleteBoard)
                        <form action="{{ route('admin.union_members.destroy', $member) }}" method="post" class="mt-2" onsubmit="return confirm('آیا از حذف این عضو اطمینان دارید؟');">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="board_editor" value="1">
                            <button type="submit" class="btn btn-outline-danger btn-sm">حذف عضو از این اتحادیه</button>
                        </form>
                    @endif
                @endif
            </details>
        @empty
            <p class="text-muted small">هنوز عضوی برای هیئت‌مدیره ثبت نشده است.</p>
        @endforelse
    @endif

    @if($canAddBoard)
        <details class="mt-4">
            <summary class="admin-secondary-btn d-inline-block" style="cursor:pointer">افزودن عضو هیئت‌مدیره</summary>
            <form class="row g-3 mt-2" action="{{ route('admin.union_members.store') }}" method="post" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="union_id" value="{{ $union->id }}">
                <input type="hidden" name="board_editor" value="1">
                <input type="hidden" name="status" value="active">
                <div class="col-md-4">
                    <label class="form-label" for="board_new_first">نام</label>
                    <input class="form-control" id="board_new_first" name="first_name" maxlength="90" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="board_new_last">نام خانوادگی</label>
                    <input class="form-control" id="board_new_last" name="last_name" maxlength="90" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="board_new_position">سمت</label>
                    <input class="form-control" id="board_new_position" name="position" maxlength="190" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="board_new_sort">ترتیب نمایش</label>
                    <input class="form-control" id="board_new_sort" name="sort_order" type="number" min="0" value="0">
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="board_new_active">وضعیت</label>
                    <select class="form-control" id="board_new_active" name="is_active">
                        <option value="1">فعال</option>
                        <option value="0">غیرفعال</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="board_new_image">عکس عضو</label>
                    <input class="form-control" id="board_new_image" name="image" type="file" accept="image/*" data-skip-media-picker>
                    <button class="admin-secondary-btn mt-2" type="button" data-media-select-target="board_new_media">انتخاب یا آپلود از کتابخانه</button>
                    <select class="d-none" id="board_new_media" name="image_media_id" tabindex="-1" aria-hidden="true">
                        <option value="">بدون عکس</option>
                        @foreach(($mediaItems ?? collect()) as $media)
                            <option value="{{ $media->id }}" data-url="{{ $media->url }}">{{ $media->title ?: $media->original_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12"><button class="admin-primary-btn" type="submit">ثبت عضو هیئت‌مدیره</button></div>
            </form>
        </details>
    @endif
</section>
@endif
