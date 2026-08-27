<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('code') | @yield('title')</title>
    <style>
        *{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;padding:24px;background:#f5f8fb;color:#23384a;font-family:Tahoma,"Segoe UI",sans-serif}.error-card{width:min(560px,100%);background:#fff;border:1px solid #dfe7ee;border-radius:18px;padding:34px;text-align:center;box-shadow:0 16px 44px rgba(34,58,80,.08)}.error-code{display:inline-flex;align-items:center;justify-content:center;min-width:80px;height:44px;padding:0 14px;border-radius:12px;background:#edf6fd;color:#1479b8;font-size:22px;font-weight:800}.error-card h1{font-size:22px;margin:18px 0 10px}.error-card p{margin:0 auto;color:#6c7f90;line-height:2;max-width:450px;font-size:14px}.error-actions{margin-top:24px;display:flex;justify-content:center;gap:10px;flex-wrap:wrap}.error-actions a{display:inline-flex;align-items:center;justify-content:center;min-height:42px;padding:0 18px;border-radius:10px;text-decoration:none;font-size:14px;font-weight:700}.error-primary{background:#1679b8;color:#fff}.error-secondary{background:#fff;color:#36566d;border:1px solid #d4e0e8}
    </style>
</head>
<body>
    <main class="error-card">
        <div class="error-code">@yield('code')</div>
        <h1>@yield('title')</h1>
        <p>@yield('message')</p>
        <div class="error-actions">
            <a class="error-primary" href="{{ url('/') }}">بازگشت به صفحه اصلی</a>
            <a class="error-secondary" href="javascript:history.back()">بازگشت به صفحه قبل</a>
        </div>
    </main>
</body>
</html>
