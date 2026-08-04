<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ConvertJalaliDates
{
    /** @var array<int, string> */
    private array $dateTimeFields = [
        'published_at',
        'starts_at',
        'expires_at',
        'session_date',
        'sent_at',
        'answered_at',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('GET')) {
            return $next($request);
        }

        $data = $request->all();

        foreach ($this->dateTimeFields as $field) {
            if (! $request->has($field)) {
                continue;
            }

            $data[$field] = jalali_to_gregorian_datetime($request->input($field), $field === 'expires_at');
        }

        if ($request->filled('session_date_jalali')) {
            $sessionDate = trim((string) $request->input('session_date_jalali'));
            $sessionTime = trim((string) $request->input('session_time', ''));
            $data['session_date_jalali'] = jalali_normalize_digits($sessionDate);
            $data['session_date'] = jalali_to_gregorian_datetime(trim($sessionDate.' '.$sessionTime));
        }

        $request->merge($data);

        return $next($request);
    }
}
