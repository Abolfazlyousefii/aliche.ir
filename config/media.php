<?php

return [
    'disk' => env('MEDIA_DISK', 'public'),
    'max_upload_kilobytes' => (int) env('MEDIA_MAX_UPLOAD_KB', 5120),
    'max_width' => (int) env('MEDIA_MAX_WIDTH', 1920),
    'max_pixels' => (int) env('MEDIA_MAX_PIXELS', 40000000),
    'webp_quality' => (int) env('MEDIA_WEBP_QUALITY', 82),
    'variant_widths' => [400, 800],
    'placeholder' => 'assets/img/asnaf-gorgan-default.jpg',
];
