@extends('frontend.layouts.app')

@section('title', 'تماس با ما | اتاق اصناف مرکز استان گلستان')
@section('meta_description', 'راه‌های ارتباط، اطلاعات تماس و ارسال پیام به اتاق اصناف مرکز استان گلستان')
@section('active_menu', 'contact')

@section('content')
@php
$phone = trim((string) $settings->get('site.phone', '۰۱۷۳۲۱۵۲۹۱۲'));
$mobile = trim((string) $settings->get('site.mobile', ''));
$email = trim((string) $settings->get('site.email', 'info@asnaf-gorgan.ir'));
$address = trim((string) $settings->get('site.address', 'گرگان، خیابان مطهری جنوبی، روبروی پمپ بنزین، ساختمان اتاق اصناف'));
$mapUrl = trim((string) $settings->get('site.map_url', ''));
$responseHours = trim((string) $settings->get('site.response_hours', $settings->get('site.working_hours', '')));

$isMeaningfulValue = static fn (string $value): bool => ! in_array($value, ['', '-', '—', 'null'], true);
$digitsToEnglish = static fn (string $value): string => strtr($value, [
'۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
'۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
'٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
'٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
]);
$phoneHref = preg_replace('/[^0-9+]/', '', $digitsToEnglish($phone));
$mobileHref = preg_replace('/[^0-9+]/', '', $digitsToEnglish($mobile));
$hasPhone = $isMeaningfulValue($phone) && $phoneHref !== '';
$hasMobile = $isMeaningfulValue($mobile) && $mobileHref !== '';
$hasEmail = $isMeaningfulValue($email) && filter_var($email, FILTER_VALIDATE_EMAIL);
$hasAddress = $isMeaningfulValue($address);
$hasMap = $isMeaningfulValue($mapUrl) && filter_var($mapUrl, FILTER_VALIDATE_URL);
$hasResponseHours = $isMeaningfulValue($responseHours);
@endphp

<div class="contact-page" data-contact-page>
    <header class="page-header contact-page-header">
        <div class="site-container">
            <nav class="breadcrumb-nav" aria-label="مسیر صفحه">
                <a href="{{ route('home') }}">خانه</a>
                <span class="breadcrumb-sep" aria-hidden="true">/</span>
                <span aria-current="page">تماس با ما</span>
            </nav>
            <h1>تماس با ما</h1>
            <p>راه‌های ارتباط با اتاق اصناف و ارسال پیام برای کارشناسان مربوطه</p>
        </div>
    </header>

    <main class="contact-page-main">
        <div class="site-container">
            <section class="contact-page-intro" aria-labelledby="contact-intro-title">
                <div class="contact-page-intro-copy">
                    <span class="contact-page-eyebrow">ارتباط با اتاق اصناف</span>
                    <h2 id="contact-intro-title">پاسخ‌گوی پرسش‌ها و پیشنهادهای شما هستیم</h2>
                    <p>پیام خود را از طریق فرم ارسال کنید. اطلاعات ثبت‌شده فقط برای بررسی درخواست و برقراری ارتباط با شما استفاده می‌شود.</p>
                </div>

                <aside class="contact-complaint-notice" aria-label="راهنمای ثبت شکایت صنفی">
                    <span class="contact-complaint-notice-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path d="M6 3h12a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Zm3 5h6M8 12h8M8 16h5"></path>
                        </svg>
                    </span>
                    <span>
                        <strong>برای شکایت صنفی اقدام می‌کنید؟</strong>
                        <small>ثبت و پیگیری شکایت از طریق سامانه اختصاصی انجام می‌شود.</small>
                    </span>
                    <a href="{{ route('complaints.create') }}">ثبت شکایت</a>
                </aside>
            </section>

            @if ($hasPhone || $hasEmail || $hasMap)
            <nav class="contact-quick-actions" aria-label="راه‌های ارتباط سریع">
                @if ($hasPhone)
                <a class="contact-quick-action" href="tel:{{ $phoneHref }}">
                    <span class="contact-quick-action-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path d="M7.1 3.5 4.8 5.8c-.8.8.2 3.8 3.1 6.7s5.9 3.9 6.7 3.1l2.3-2.3 3.3 3.3-1.5 1.5c-2.3 2.3-7.5.7-12-3.8S.6 4.6 2.9 2.3L4.4.8l2.7 2.7Z"></path>
                        </svg>
                    </span>
                    <span><small>تماس مستقیم</small><strong>{{ $phone }}</strong></span>
                </a>
                @endif

                @if ($hasEmail)
                <a class="contact-quick-action" href="mailto:{{ $email }}">
                    <span class="contact-quick-action-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path d="M3 5h18v14H3V5Zm1.5 1.5L12 13l7.5-6.5M4.5 17.5l5.3-5m9.7 5-5.3-5"></path>
                        </svg>
                    </span>
                    <span><small>پست الکترونیکی</small><strong dir="ltr">{{ $email }}</strong></span>
                </a>
                @endif

                @if ($hasMap)
                <a class="contact-quick-action" href="{{ $mapUrl }}" target="_blank" rel="noopener noreferrer">
                    <span class="contact-quick-action-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path d="M12 21s7-6.2 7-12A7 7 0 1 0 5 9c0 5.8 7 12 7 12Zm0-9.5A2.5 2.5 0 1 1 12 6a2.5 2.5 0 0 1 0 5.5Z"></path>
                        </svg>
                    </span>
                    <span><small>مسیریابی</small><strong>مشاهده موقعیت اتاق</strong></span>
                </a>
                @endif
            </nav>
            @endif

            <div class="contact-page-layout">
                <section class="contact-form-card" aria-labelledby="contact-form-title">
                    <div class="contact-card-heading">
                        <span class="contact-card-heading-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <path d="M4 5h16v12H8l-4 4V5Zm4 4h8M8 13h5"></path>
                            </svg>
                        </span>
                        <div>
                            <h2 id="contact-form-title">فرم ارسال پیام</h2>
                            <p>فیلدهای ستاره‌دار الزامی هستند.</p>
                        </div>
                    </div>

                    <div class="contact-form-status" data-contact-form-status aria-live="polite" tabindex="-1">
                        @if (session('success'))
                        <div class="contact-alert contact-alert-success" role="status">
                            <span aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <path d="m5 12 4 4L19 6"></path>
                                </svg>
                            </span>
                            <p>{{ session('success') }}</p>
                        </div>
                        @endif

                        @if ($errors->any())
                        <div class="contact-alert contact-alert-danger" role="alert">
                            <span aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <path d="M12 9v4m0 4h.01M12 3 2.5 20h19L12 3Z"></path>
                                </svg>
                            </span>
                            <div>
                                <strong>لطفاً موارد مشخص‌شده را اصلاح کنید.</strong>
                                @if ($errors->has('form'))
                                <p>{{ $errors->first('form') }}</p>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>

                    <form class="contact-form" action="{{ route('contact.store') }}" method="POST" data-contact-form>
                        @csrf

                        <div class="contact-honeypot" aria-hidden="true">
                            <label for="website">وب‌سایت</label>
                            <input id="website" name="website" type="text" tabindex="-1" autocomplete="off">
                        </div>

                        <div class="contact-form-grid">
                            <div class="contact-field">
                                <label for="full_name">نام و نام خانوادگی <span aria-hidden="true">*</span></label>
                                <input
                                    class="contact-control @error('full_name') is-invalid @enderror"
                                    id="full_name"
                                    name="full_name"
                                    value="{{ old('full_name') }}"
                                    required
                                    maxlength="255"
                                    autocomplete="name"
                                    @error('full_name') aria-invalid="true" aria-describedby="full_name_error" @enderror>
                                @error('full_name')<span class="contact-field-error" id="full_name_error">{{ $message }}</span>@enderror
                            </div>

                            <div class="contact-field">
                                <label for="mobile">شماره تماس <span aria-hidden="true">*</span></label>
                                <input
                                    class="contact-control @error('mobile') is-invalid @enderror"
                                    id="mobile"
                                    name="mobile"
                                    value="{{ old('mobile') }}"
                                    required
                                    maxlength="20"
                                    autocomplete="tel"
                                    inputmode="tel"
                                    dir="ltr"
                                    @error('mobile') aria-invalid="true" aria-describedby="mobile_error" @enderror>
                                <small class="contact-field-help">شماره همراه یا تلفن ثابت همراه با پیش‌شماره</small>
                                @error('mobile')<span class="contact-field-error" id="mobile_error">{{ $message }}</span>@enderror
                            </div>

                            <div class="contact-field">
                                <label for="email">ایمیل <small>(اختیاری)</small></label>
                                <input
                                    class="contact-control @error('email') is-invalid @enderror"
                                    id="email"
                                    name="email"
                                    type="email"
                                    value="{{ old('email') }}"
                                    maxlength="255"
                                    autocomplete="email"
                                    inputmode="email"
                                    dir="ltr"
                                    @error('email') aria-invalid="true" aria-describedby="email_error" @enderror>
                                @error('email')<span class="contact-field-error" id="email_error">{{ $message }}</span>@enderror
                            </div>

                            <div class="contact-field">
                                <label for="subject">موضوع <span aria-hidden="true">*</span></label>
                                <input
                                    class="contact-control @error('subject') is-invalid @enderror"
                                    id="subject"
                                    name="subject"
                                    value="{{ old('subject') }}"
                                    required
                                    maxlength="255"
                                    autocomplete="off"
                                    @error('subject') aria-invalid="true" aria-describedby="subject_error" @enderror>
                                @error('subject')<span class="contact-field-error" id="subject_error">{{ $message }}</span>@enderror
                            </div>

                            <div class="contact-field contact-field-full">
                                <div class="contact-field-label-row">
                                    <label for="message">متن پیام <span aria-hidden="true">*</span></label>
                                    <output class="contact-message-counter" for="message" data-message-counter>{{ mb_strlen((string) old('message')) }} از ۵۰۰۰</output>
                                </div>
                                <textarea
                                    class="contact-control contact-textarea @error('message') is-invalid @enderror"
                                    id="message"
                                    name="message"
                                    rows="6"
                                    required
                                    minlength="10"
                                    maxlength="5000"
                                    @error('message') aria-invalid="true" aria-describedby="message_error" @enderror>{{ old('message') }}</textarea>
                                <small class="contact-field-help">پیام خود را شفاف و با جزئیات لازم بنویسید.</small>
                                @error('message')<span class="contact-field-error" id="message_error">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        <div class="contact-form-footer">
                            <p class="contact-privacy-note">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M7 10V8a5 5 0 0 1 10 0v2m-11 0h12v10H6V10Z"></path>
                                </svg>
                                اطلاعات شما محرمانه می‌ماند و فقط برای پاسخ‌گویی به همین پیام استفاده می‌شود.
                            </p>
                            <button class="contact-submit-button" type="submit" data-contact-submit>
                                <span class="contact-submit-spinner" aria-hidden="true"></span>
                                <span data-contact-submit-label>ارسال پیام</span>
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="m5 12 14-7-4 14-3-6-7-1Zm7 1 7-8"></path>
                                </svg>
                            </button>
                        </div>
                    </form>
                </section>

                <aside class="contact-info-column" aria-labelledby="contact-info-title">
                    <section class="contact-info-card">
                        <div class="contact-card-heading contact-card-heading-compact">
                            <span class="contact-card-heading-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <path d="M5 4h14v16H5V4Zm4 0v4m6-4v4M8 12h8M8 16h5"></path>
                                </svg>
                            </span>
                            <div>
                                <h2 id="contact-info-title">اطلاعات تماس</h2>
                                <p>راه‌های رسمی ارتباط با اتاق اصناف</p>
                            </div>
                        </div>

                        <div class="contact-info-list">
                            @if ($hasPhone)
                            <a class="contact-info-item" href="tel:{{ $phoneHref }}">
                                <span class="contact-info-icon" aria-hidden="true"><svg viewBox="0 0 24 24">
                                        <path d="M7.1 3.5 4.8 5.8c-.8.8.2 3.8 3.1 6.7s5.9 3.9 6.7 3.1l2.3-2.3 3.3 3.3-1.5 1.5c-2.3 2.3-7.5.7-12-3.8S.6 4.6 2.9 2.3L4.4.8l2.7 2.7Z"></path>
                                    </svg></span>
                                <span><small>تلفن اتاق</small><strong>{{ $phone }}</strong></span>
                                <svg class="contact-info-arrow" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="m14 6-6 6 6 6"></path>
                                </svg>
                            </a>
                            @endif

                            @if ($hasMobile)
                            <a class="contact-info-item" href="tel:{{ $mobileHref }}">
                                <span class="contact-info-icon" aria-hidden="true"><svg viewBox="0 0 24 24">
                                        <rect x="7" y="2" width="10" height="20" rx="2"></rect>
                                        <path d="M10 5h4m-3 14h2"></path>
                                    </svg></span>
                                <span><small>شماره همراه</small><strong>{{ $mobile }}</strong></span>
                                <svg class="contact-info-arrow" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="m14 6-6 6 6 6"></path>
                                </svg>
                            </a>
                            @endif

                            @if ($hasEmail)
                            <a class="contact-info-item" href="mailto:{{ $email }}">
                                <span class="contact-info-icon" aria-hidden="true"><svg viewBox="0 0 24 24">
                                        <path d="M3 5h18v14H3V5Zm1.5 1.5L12 13l7.5-6.5"></path>
                                    </svg></span>
                                <span><small>پست الکترونیکی</small><strong dir="ltr">{{ $email }}</strong></span>
                                <svg class="contact-info-arrow" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="m14 6-6 6 6 6"></path>
                                </svg>
                            </a>
                            @endif

                            @if ($hasAddress)
                            <div class="contact-info-item contact-info-item-static">
                                <span class="contact-info-icon" aria-hidden="true"><svg viewBox="0 0 24 24">
                                        <path d="M12 21s7-6.2 7-12A7 7 0 1 0 5 9c0 5.8 7 12 7 12Zm0-9.5A2.5 2.5 0 1 1 12 6a2.5 2.5 0 0 1 0 5.5Z"></path>
                                    </svg></span>
                                <span><small>نشانی</small><strong>{{ $address }}</strong></span>
                            </div>
                            @endif

                            @if ($hasResponseHours)
                            <div class="contact-info-item contact-info-item-static">
                                <span class="contact-info-icon" aria-hidden="true"><svg viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="9"></circle>
                                        <path d="M12 7v5l3 2"></path>
                                    </svg></span>
                                <span><small>ساعت پاسخ‌گویی</small><strong>{{ $responseHours }}</strong></span>
                            </div>
                            @endif
                        </div>

                        @if ($hasMap)
                        <a class="contact-map-button" href="{{ $mapUrl }}" target="_blank" rel="noopener noreferrer">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="m3 6 6-3 6 3 6-3v15l-6 3-6-3-6 3V6Zm6-3v15m6-12v15"></path>
                            </svg>
                            مشاهده موقعیت و مسیریابی
                        </a>
                        @endif
                    </section>

                    <section class="contact-support-card">
                        <span class="contact-support-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <path d="M4 12a8 8 0 0 1 16 0v5a2 2 0 0 1-2 2h-2v-7h4M4 12v7h4v-7H4Zm8 8h3"></path>
                            </svg>
                        </span>
                        <div>
                            <h2>پیگیری درخواست</h2>
                            <p>در متن پیام، شماره تماس و موضوع درخواست را دقیق وارد کنید تا پیگیری سریع‌تر انجام شود.</p>
                        </div>
                    </section>
                </aside>
            </div>
        </div>
    </main>
</div>
@endsection