<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Services\SlugRedirectService;

class PageController extends Controller
{
    public function show(string $slug): View|RedirectResponse
    {
        if ($redirect = app(SlugRedirectService::class)->redirectIfLegacy(Page::class, $slug, 'pages.show')) {
            return $redirect;
        }

        $page = Page::query()
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        return view('frontend.pages.show', compact('page'));
    }
}
