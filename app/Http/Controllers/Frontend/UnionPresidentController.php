<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\GuildUnion;
use Illuminate\View\View;

class UnionPresidentController extends Controller
{
    public function index(): View
    {
        $unions = GuildUnion::query()
            ->active()
            ->with('unionType')
            ->whereNotNull('manager_name')
            ->where('manager_name', '!=', '')
            ->orderBy('title')
            ->orderBy('name')
            ->get();

        return view('frontend.union_presidents.index', compact('unions'));
    }
}
