<?php

namespace App\Http\Middleware;

use App\Support\Features;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureComplaintsEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Features::complaintsEnabled()) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'ثبت و پیگیری شکایت در حال حاضر غیرفعال است.'], 404);
        }

        // 404 keeps the disabled pages out of search engine indexes.
        return response()->view('frontend.complaints.disabled', [], 404);
    }
}
