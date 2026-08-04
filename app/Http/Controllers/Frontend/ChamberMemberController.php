<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ChamberMember;
use Illuminate\View\View;

class ChamberMemberController extends Controller
{
    public function index(): View
    {
        return view('frontend.chamber_members.index', [
            'members' => ChamberMember::query()->where('is_active', true)->orderBy('sort_order')->latest()->get(),
        ]);
    }
}
