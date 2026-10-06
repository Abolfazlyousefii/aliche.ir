@extends('admin.layouts.app')

@section('title', 'محتواهای در انتظار انتشار')

@section('content')
    <div class="admin-page-toolbar">
        <div>
            <p class="admin-eyebrow">گردش‌کار انتشار</p>
            <h2>محتوای نیازمند بررسی</h2>
            <p class="text-muted mb-0">فقط محتوای ماژول‌هایی که مجوز بررسی آن‌ها را دارید نمایش داده می‌شود.</p>
        </div>
        <span class="admin-list-total">{{ fa_number($items->count()) }} مورد در فهرست</span>
    </div>

    <section class="admin-panel-card admin-list-filter-card mb-3" aria-label="فیلتر محتواهای در انتظار">
        <form method="GET" action="{{ route('admin.pending_approvals.index') }}" class="admin-list-filter-form" role="search">
            <div class="admin-list-primary-search">
                <div class="admin-list-primary-search__field">
                    <label for="pending-content-search">جستجوی محتوا</label>
                    <input id="pending-content-search" class="form-control" type="search" name="search" value="{{ $search }}" placeholder="عنوان یا خلاصه محتوا...">
                </div>
                <button class="admin-primary-btn" type="submit">جستجو</button>
                @if($search !== '' || $type !== '')
                    <a href="{{ route('admin.pending_approvals.index') }}" class="admin-secondary-btn">پاک‌کردن فیلترها</a>
                @endif
            </div>
            <div class="admin-list-extra-grid">
                <div class="admin-list-extra-field">
                    <label for="pending-content-type">نوع محتوا</label>
                    <select id="pending-content-type" class="form-select" name="type">
                        <option value="">همه انواع مجاز</option>
                        @foreach($typeOptions as $typeCode => $typeLabel)
                            <option value="{{ $typeCode }}" @selected($type === $typeCode)>{{ $typeLabel }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="admin-list-extra-apply">
                    <button class="admin-primary-btn" type="submit">اعمال فیلتر</button>
                </div>
            </div>
        </form>
        <p class="admin-pending-summary">نمایش {{ fa_number($items->count()) }} مورد از {{ fa_number($totalVisibleItems) }} محتوای قابل بررسی</p>
    </section>

    <div class="admin-panel-card">
        <div>
            @if($items->isNotEmpty())
                <div class="table-responsive admin-list-responsive-table" tabindex="0" role="region" aria-label="محتوای در انتظار بررسی">
                    <table class="table admin-table admin-list-table align-middle mb-0" data-admin-list-table>
                        <thead class="table-light">
                            <tr>
                                <th>عنوان</th>
                                <th>نوع محتوا</th>
                                <th>خلاصه</th>
                                <th>تاریخ ایجاد</th>
                                <th class="text-center">عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $item)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            @if($item['image'])
                                                <img src="{{ $item['image'] }}" alt="{{ $item['title'] }}" class="rounded" width="56" height="56" style="object-fit: cover;">
                                            @endif
                                            <strong>{{ $item['title'] }}</strong>
                                        </div>
                                    </td>
                                    <td><span class="badge text-bg-secondary">{{ $item['label'] }}</span></td>
                                    <td class="text-muted" style="max-width: 360px;">{{ $item['summary'] }}</td>
                                    <td>{{ jalali_datetime($item['created_at'] ?? null) ?? '-' }}</td>
                                    <td>
                                        <div class="d-flex flex-wrap justify-content-center gap-2">
                                            @if($item['show_url'])
                                                <a href="{{ $item['show_url'] }}" class="btn btn-sm btn-outline-secondary">مشاهده</a>
                                            @endif
                                            @if($item['can_publish'] ?? false)
                                            <form method="POST" action="{{ route('admin.pending_approvals.publish', [$item['type'], $item['model']->getKey()]) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button class="btn btn-sm btn-success" type="submit">تایید و انتشار</button>
                                            </form>
                                            @endif
                                            @if($item['can_approve'] ?? false)
                                            <form method="POST" action="{{ route('admin.pending_approvals.reject', [$item['type'], $item['model']->getKey()]) }}" class="d-flex gap-1">
                                                @csrf
                                                @method('PATCH')
                                                <input type="text" name="rejected_reason" class="form-control form-control-sm" placeholder="دلیل رد" required style="min-width: 180px;">
                                                <button class="btn btn-sm btn-danger" type="submit">رد</button>
                                            </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-5 text-center text-muted">در حال حاضر محتوایی در انتظار انتشار وجود ندارد.</div>
            @endif
        </div>
    </div>
@endsection
