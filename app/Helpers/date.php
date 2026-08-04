<?php

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

if (! function_exists('jalali_normalize_digits')) {
    function jalali_normalize_digits(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return strtr($value, [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);
    }
}

if (! function_exists('jalali_to_persian_digits')) {
    function jalali_to_persian_digits(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return strtr($value, [
            '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
            '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹',
        ]);
    }
}

if (! class_exists('JalaliDate')) {
    final class JalaliDate
    {
        public function __construct(private readonly mixed $date)
        {
        }

        public function format(string $format = 'Y/m/d'): string
        {
            return jalali_format($this->date, $format) ?? '';
        }

        public function persianFormat(string $format = 'Y/m/d'): string
        {
            return jalali_to_persian_digits($this->format($format)) ?? '';
        }
    }
}

if (! function_exists('jdate')) {
    function jdate(mixed $date = null): JalaliDate
    {
        return new JalaliDate($date ?? now('Asia/Tehran'));
    }
}

if (! function_exists('jalali_text_date')) {
    function jalali_text_date(mixed $date = null): string
    {
        $carbon = jalali_carbon($date ?? now('Asia/Tehran'));

        if (! $carbon) {
            return '';
        }

        [$year, $month, $day] = gregorian_to_jalali_parts((int) $carbon->format('Y'), (int) $carbon->format('n'), (int) $carbon->format('j'));

        $monthNames = [
            1 => 'فروردین',
            2 => 'اردیبهشت',
            3 => 'خرداد',
            4 => 'تیر',
            5 => 'مرداد',
            6 => 'شهریور',
            7 => 'مهر',
            8 => 'آبان',
            9 => 'آذر',
            10 => 'دی',
            11 => 'بهمن',
            12 => 'اسفند',
        ];

        return jalali_to_persian_digits(sprintf('%d %s %d', $day, $monthNames[$month], $year)) ?? '';
    }
}

if (! function_exists('gregorian_to_jalali_parts')) {
    /** @return array{0:int,1:int,2:int} */
    function gregorian_to_jalali_parts(int $gy, int $gm, int $gd): array
    {
        $gdm = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
        $gy2 = $gm > 2 ? $gy + 1 : $gy;
        $days = 355666 + (365 * $gy) + intdiv($gy2 + 3, 4) - intdiv($gy2 + 99, 100) + intdiv($gy2 + 399, 400) + $gd + $gdm[$gm - 1];
        $jy = -1595 + (33 * intdiv($days, 12053));
        $days %= 12053;
        $jy += 4 * intdiv($days, 1461);
        $days %= 1461;

        if ($days > 365) {
            $jy += intdiv($days - 1, 365);
            $days = ($days - 1) % 365;
        }

        if ($days < 186) {
            $jm = 1 + intdiv($days, 31);
            $jd = 1 + ($days % 31);
        } else {
            $jm = 7 + intdiv($days - 186, 30);
            $jd = 1 + (($days - 186) % 30);
        }

        return [$jy, $jm, $jd];
    }
}

if (! function_exists('jalali_to_gregorian_parts')) {
    /** @return array{0:int,1:int,2:int} */
    function jalali_to_gregorian_parts(int $jy, int $jm, int $jd): array
    {
        $jy += 1595;
        $days = -355668 + (365 * $jy) + (intdiv($jy, 33) * 8) + intdiv(($jy % 33) + 3, 4) + $jd;
        $days += $jm < 7 ? (($jm - 1) * 31) : ((($jm - 7) * 30) + 186);
        $gy = 400 * intdiv($days, 146097);
        $days %= 146097;

        if ($days > 36524) {
            $gy += 100 * intdiv(--$days, 36524);
            $days %= 36524;

            if ($days >= 365) {
                $days++;
            }
        }

        $gy += 4 * intdiv($days, 1461);
        $days %= 1461;

        if ($days > 365) {
            $gy += intdiv($days - 1, 365);
            $days = ($days - 1) % 365;
        }

        $gd = $days + 1;
        $sal = [0, 31, (($gy % 4 === 0 && $gy % 100 !== 0) || ($gy % 400 === 0)) ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

        for ($gm = 1; $gm <= 12 && $gd > $sal[$gm]; $gm++) {
            $gd -= $sal[$gm];
        }

        return [$gy, $gm, $gd];
    }
}

if (! function_exists('jalali_carbon')) {
    function jalali_carbon(mixed $date): ?Carbon
    {
        if (blank($date)) {
            return null;
        }

        if ($date instanceof Carbon) {
            return $date;
        }

        if ($date instanceof \DateTimeInterface) {
            return Carbon::instance($date);
        }

        if (is_string($date)) {
            $normalized = trim(jalali_normalize_digits($date));
            if (preg_match('/^(\d{4})[\/-](\d{1,2})[\/-](\d{1,2})/', $normalized, $matches) === 1 && (int) $matches[1] < 1700) {
                $converted = jalali_to_gregorian_datetime($normalized);
                if ($converted !== $normalized) {
                    $date = $converted;
                }
            }
        }

        try {
            return Carbon::parse($date);
        } catch (Throwable) {
            return null;
        }
    }
}

if (! function_exists('jalali_format')) {
    function jalali_format(mixed $date, string $format = 'Y/m/d'): ?string
    {
        $carbon = jalali_carbon($date);

        if (! $carbon) {
            return null;
        }

        [$jy, $jm, $jd] = gregorian_to_jalali_parts((int) $carbon->format('Y'), (int) $carbon->format('n'), (int) $carbon->format('j'));

        $replacements = [
            'Y' => sprintf('%04d', $jy),
            'y' => substr((string) $jy, -2),
            'm' => sprintf('%02d', $jm),
            'n' => (string) $jm,
            'd' => sprintf('%02d', $jd),
            'j' => (string) $jd,
            'H' => $carbon->format('H'),
            'i' => $carbon->format('i'),
            's' => $carbon->format('s'),
        ];

        return strtr($format, $replacements);
    }
}

if (! function_exists('fa_number')) {
    function fa_number(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_float($value) || is_int($value)) {
            $value = number_format($value);
        }

        return jalali_to_persian_digits((string) $value) ?? '';
    }
}

if (! function_exists('jalali_date')) {
    function jalali_date(mixed $date, string $format = 'Y/m/d'): ?string
    {
        $formatted = jalali_format($date, $format);

        return $formatted === null ? null : fa_number($formatted);
    }
}

if (! function_exists('jalali_datetime')) {
    function jalali_datetime(mixed $date, string $format = 'Y/m/d H:i'): ?string
    {
        $formatted = jalali_format($date, $format);

        return $formatted === null ? null : fa_number($formatted);
    }
}

if (! function_exists('jalali_input_date')) {
    function jalali_input_date(mixed $date): ?string
    {
        return jalali_date($date, 'Y/m/d');
    }
}

if (! function_exists('jalali_input_datetime')) {
    function jalali_input_datetime(mixed $date): ?string
    {
        return jalali_datetime($date, 'Y/m/d H:i');
    }
}

if (! function_exists('jalali_to_gregorian_datetime')) {
    function jalali_to_gregorian_datetime(mixed $value, bool $endOfDay = false): ?string
    {
        if (blank($value)) {
            return null;
        }

        if ($value instanceof CarbonInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        $value = trim(jalali_normalize_digits((string) $value));
        $value = str_replace('T', ' ', $value);

        if (preg_match('/^(\d{4})[\/-](\d{1,2})[\/-](\d{1,2})(?:\s+(\d{1,2}):(\d{1,2})(?::(\d{1,2}))?)?$/', $value, $matches) !== 1) {
            return $value;
        }

        $year = (int) $matches[1];
        $month = (int) $matches[2];
        $day = (int) $matches[3];
        $hour = isset($matches[4]) ? (int) $matches[4] : ($endOfDay ? 23 : 0);
        $minute = isset($matches[5]) ? (int) $matches[5] : ($endOfDay ? 59 : 0);
        $second = isset($matches[6]) ? (int) $matches[6] : ($endOfDay ? 59 : 0);

        if ($year < 1500) {
            [$gy, $gm, $gd] = jalali_to_gregorian_parts($year, $month, $day);

            return sprintf('%04d-%02d-%02d %02d:%02d:%02d', $gy, $gm, $gd, $hour, $minute, $second);
        }

        return sprintf('%04d-%02d-%02d %02d:%02d:%02d', $year, $month, $day, $hour, $minute, $second);
    }
}


if (! function_exists('image_url')) {
    function image_url(?string $path, string $fallback = 'assets/img/asnaf-gorgan-default.jpg'): string
    {
        $path = trim((string) $path);

        if ($path === '') {
            return asset($fallback);
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return \App\Support\PublicFileUrl::make($path, $fallback);
        }

        if (str_starts_with($path, '/storage/') || str_starts_with($path, 'storage/') || str_starts_with($path, 'public/')) {
            return \App\Support\PublicFileUrl::make($path, $fallback);
        }

        if (str_starts_with($path, '/')) {
            return $path;
        }

        if (str_starts_with($path, 'assets/')) {
            return asset($path);
        }

        $path = preg_replace('#^(public/|storage/)#', '', $path);

        return \App\Support\PublicFileUrl::make($path, $fallback);
    }
}
