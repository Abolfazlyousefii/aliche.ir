<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\ChamberMember;
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

        if ($page->isChamberAboutPage()) {
            $members = ChamberMember::query()->where('is_active', true)
                ->orderBy('sort_order')->orderBy('id')->get();

            return view('frontend.pages.chamber-about', [
                'page' => $page,
                'president' => $members->firstWhere('is_president', true),
                'members' => $members->where('is_president', false),
            ]);
        }

        return view('frontend.pages.show', compact('page'));
    }
}
