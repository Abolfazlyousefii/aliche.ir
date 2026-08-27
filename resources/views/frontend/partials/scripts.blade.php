@php
    $mainJsPath = public_path('assets/js/main.js');
    $ajaxCoreJsPath = public_path('assets/js/ajax-core.js');
    $mainJsVersion = is_file($mainJsPath) ? filemtime($mainJsPath) : '1';
    $ajaxCoreJsVersion = is_file($ajaxCoreJsPath) ? filemtime($ajaxCoreJsPath) : '1';
@endphp

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="{{ asset('assets/js/main.js') }}?v={{ $mainJsVersion }}"></script>
<script src="{{ asset('assets/js/ajax-core.js') }}?v={{ $ajaxCoreJsVersion }}"></script>
