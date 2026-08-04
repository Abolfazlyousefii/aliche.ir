@if (session('success'))
    <div class="alert alert-success admin-alert" role="alert">{{ session('success') }}</div>
@endif

@if (session('error'))
    <div class="alert alert-danger admin-alert" role="alert">{{ session('error') }}</div>
@endif

@if ($errors->any())
    <div class="alert alert-danger admin-alert" role="alert">
        <strong>لطفاً خطاهای زیر را بررسی کنید:</strong>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
