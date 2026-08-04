<?php

namespace App\Services;

use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;

class DailyNewsService
{
    public function selectedDate(?string $jalaliDate = null): Carbon
    {
        if (blank($jalaliDate)) {
            return now()->startOfDay();
        }

        $normalized = str_replace('/', '-', jalali_normalize_digits($jalaliDate));
        if (preg_match('/^\d{4}-\d{1,2}-\d{1,2}$/', $normalized) !== 1) {
            throw new InvalidArgumentException('تاریخ واردشده معتبر نیست.');
        }

        return Carbon::parse(jalali_to_gregorian_datetime($normalized))->startOfDay();
    }

    public function jalaliParam(Carbon $date): string
    {
        return str_replace('/', '-', jalali_input_date($date));
    }

    public function label(Carbon $date): string
    {
        $weekdays = ['یکشنبه','دوشنبه','سه‌شنبه','چهارشنبه','پنجشنبه','جمعه','شنبه'];
        return ($weekdays[$date->dayOfWeek] ?? '').' '.jalali_text_date($date);
    }

    public function home(Carbon $date, int $limit = 10): Collection
    {
        return Cache::remember($this->cacheKey($date, 'home'), now()->addMinutes(10), fn () => $this->baseQuery($date)
            ->limit($limit)
            ->get());
    }

    public function count(Carbon $date): int
    {
        return Cache::remember($this->cacheKey($date, 'count'), now()->addMinutes(10), fn () => $this->baseQuery($date)->count());
    }

    public function paginate(Carbon $date, int $perPage = 15): LengthAwarePaginator
    {
        return $this->baseQuery($date)->paginate($perPage)->withQueryString();
    }

    public function cacheKey(Carbon $date, string $scope): string
    {
        return "{$scope}.daily_news.".$date->toDateString();
    }

    public function forgetFor(?Carbon $date): void
    {
        if (! $date) { return; }
        Cache::forget($this->cacheKey($date, 'home'));
        Cache::forget($this->cacheKey($date, 'count'));
        Cache::forget('home.daily_posts.'.$date->toDateString());
    }

    private function baseQuery(Carbon $date)
    {
        return Post::query()
            ->published()
            ->publishedOn($date)
            ->with(['category', 'featuredMedia', 'author'])
            ->orderByDesc('published_at')
            ->orderByDesc('id');
    }
}
