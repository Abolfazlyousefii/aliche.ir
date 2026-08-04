<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\DailyNewsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DailyNewsController extends Controller
{
    public function index(Request $request, DailyNewsService $dailyNews): View
    {
        $selectedDate = $dailyNews->selectedDate($request->query('date'));
        abort_if($selectedDate->isFuture(), 404);

        return view('frontend.daily-news.index', [
            'dailyPosts' => $dailyNews->paginate($selectedDate),
            'selectedDate' => $selectedDate,
            'selectedDateLabel' => $dailyNews->label($selectedDate),
            'selectedDateParam' => $dailyNews->jalaliParam($selectedDate),
            'previousDateParam' => $dailyNews->jalaliParam($selectedDate->copy()->subDay()),
            'nextDateParam' => $dailyNews->jalaliParam($selectedDate->copy()->addDay()),
            'isToday' => $selectedDate->isSameDay(now()),
            'dailyNewsCount' => $dailyNews->count($selectedDate),
        ]);
    }
}
