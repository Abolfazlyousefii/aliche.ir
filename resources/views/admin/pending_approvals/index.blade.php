@extends('admin.layouts.app')

@section('title', 'محتواهای در انتظار انتشار')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h3 mb-1">محتواهای در انتظار انتشار</h1>
            <p class="text-muted mb-0">همه محتواهای در انتظار بررسی از ماژول‌های محتوایی اینجا نمایش داده می‌شوند و با یک دکمه هم تایید و هم منتشر می‌شوند.</p>
        </div>
        <span class="badge text-bg-warning fs-6">{{ $items->count() }} مورد</span>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            @if($items->isNotEmpty())
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
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
                                            <form method="POST" action="{{ route('admin.pending_approvals.publish', [$item['type'], $item['model']->getKey()]) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button class="btn btn-sm btn-success" type="submit">تایید و انتشار</button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.pending_approvals.reject', [$item['type'], $item['model']->getKey()]) }}" class="d-flex gap-1">
                                                @csrf
                                                @method('PATCH')
                                                <input type="text" name="rejected_reason" class="form-control form-control-sm" placeholder="دلیل رد" required style="min-width: 180px;">
                                                <button class="btn btn-sm btn-danger" type="submit">رد</button>
                                            </form>
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
