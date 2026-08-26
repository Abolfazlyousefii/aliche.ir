@extends('admin.layouts.app')

@section('title', 'مدیریت کش')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-xl-7 col-lg-9">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-md-5">
                    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-4">
                        <div>
                            <h1 class="h4 mb-2">مدیریت کش سایت</h1>
                            <p class="text-muted mb-0">
                                بعد از آپلود فایل‌های Blade، Route یا تنظیمات، از این بخش کش Laravel را پاک کنید.
                            </p>
                        </div>
                        <span class="badge text-bg-light border">فقط مدیران مجاز</span>
                    </div>

                    <div class="alert alert-warning">
                        این عملیات محتوا یا اطلاعات دیتابیس را حذف نمی‌کند؛ فقط کش‌های Laravel
                        شامل View، Route، Config و Application Cache پاک می‌شوند.
                    </div>

                    <form method="POST" action="{{ route('admin.cache.clear') }}">
                        @csrf
                        <button type="submit" class="btn btn-primary px-4">
                            پاک‌سازی کش Laravel
                        </button>
                    </form>

                    <div class="mt-4 pt-3 border-top">
                        <small class="text-muted">
                            آدرس ثابت این صفحه:
                            <code>{{ url('/admin/cache') }}</code>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
