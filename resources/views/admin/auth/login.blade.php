<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ورود به پنل مدیریت | اتاق اصناف مرکز استان گلستان</title>
    <link href="https://cdn.jsdelivr.net" rel="preconnect">
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@100..900&display=swap" rel="stylesheet">
    <link href="{{ asset('assets/admin/css/admin.css') }}?v={{ filemtime(public_path('assets/admin/css/admin.css')) }}" rel="stylesheet">
</head>
<body class="admin-auth-body">
    <main class="admin-auth-shell">
        <section class="admin-auth-card" aria-labelledby="adminLoginTitle">
            <div class="admin-auth-brand">
                <div class="admin-brand-mark">ا</div>
                <div>
                    <p class="admin-eyebrow">سامانه مدیریت محتوا</p>
                    <h1 id="adminLoginTitle">ورود به پنل مدیریت</h1>
                </div>
            </div>

            @include('admin.partials.alerts')

            <form class="admin-form" action="{{ route('login') }}" method="POST" novalidate>
                @csrf
                <div>
                    <label class="form-label" for="mobile">شماره تماس مدیر</label>
                    <input class="form-control @error('mobile') is-invalid @enderror" dir="ltr" id="mobile" name="mobile" type="tel" value="{{ old('mobile') }}" autocomplete="tel" inputmode="tel" placeholder="09110000000" required autofocus>
                    @error('mobile')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div>
                    <label class="form-label" for="password">رمز عبور</label>
                    <input class="form-control @error('password') is-invalid @enderror" id="password" name="password" type="password" autocomplete="current-password" placeholder="رمز عبور حساب مدیریت" required>
                    @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    <div class="form-text">اطلاعات ورود را از مدیر سامانه دریافت کنید.</div>
                </div>

                <label class="form-check admin-auth-check" for="remember">
                    <input class="form-check-input" id="remember" name="remember" type="checkbox" value="1">
                    <span class="form-check-label">مرا به خاطر بسپار</span>
                </label>

                <button class="admin-primary-btn w-100" type="submit">ورود به پنل</button>
                <a class="admin-secondary-btn w-100" href="{{ route('home') }}">بازگشت به سایت</a>
            </form>
        </section>
    </main>
</body>
</html>
