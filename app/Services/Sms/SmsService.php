<?php

namespace App\Services\Sms;

use Illuminate\Support\Str;

class SmsService
{
    /**
     * @param  iterable<int, string>  $recipients
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function send(string $message, iterable $recipients, array $context = []): array
    {
        $numbers = collect($recipients)->filter()->values();

        return [
            'fake' => true,
            'status' => 'sent',
            'message' => 'ارسال واقعی انجام نشد؛ پاسخ شبیه‌سازی‌شده برای آماده‌سازی اتصال پنل پیامکی است.',
            'tracking_id' => 'FAKE-'.Str::upper(Str::random(10)),
            'recipient_count' => $numbers->count(),
            'recipients' => $numbers->all(),
            'context' => $context,
            'sent_at' => now()->toISOString(),
        ];
    }
}
