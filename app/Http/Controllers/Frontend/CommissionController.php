<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Services\SlugRedirectService;

class CommissionController extends Controller
{
    public function index(): View
    {
        $commissions = Commission::query()
            ->published()
            ->withCount(['publishedSessions as sessions_count'])
            ->orderBy('sort_order')
            ->latest()
            ->paginate(12);

        return view('frontend.commissions.index', compact('commissions'));
    }

    public function show(string $slug): View|RedirectResponse
    {
        if ($redirect = app(SlugRedirectService::class)->redirectIfLegacy(Commission::class, $slug, 'commissions.show')) {
            return $redirect;
        }

        $commission = Commission::query()
            ->published()
            ->with(['activeTasks', 'publishedSessions' => fn ($query) => $query->orderByDesc('session_date')])
            ->where('slug', $slug)
            ->firstOrFail();

        return view('frontend.commissions.show', compact('commission'));
    }
}
