<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Services\SettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function create(SettingService $settings): View
    {
        return view('frontend.contact.create', compact('settings'));
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        // A lightweight honeypot blocks simple form bots without adding friction for visitors.
        if ($request->filled('website')) {
            return $this->failedSubmissionResponse($request);
        }

        $request->merge([
            'full_name' => trim((string) $request->input('full_name')),
            'mobile' => trim((string) $request->input('mobile')),
            'email' => $request->filled('email') ? trim((string) $request->input('email')) : null,
            'subject' => trim((string) $request->input('subject')),
            'message' => trim((string) $request->input('message')),
        ]);

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'mobile' => ['required', 'string', 'max:20', 'regex:/^[0-9۰-۹٠-٩+\-\s()]{8,20}$/u'],
            'email' => ['nullable', 'email:rfc', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
        ], [], [
            'full_name' => 'نام و نام خانوادگی',
            'mobile' => 'شماره تماس',
            'email' => 'ایمیل',
            'subject' => 'موضوع',
            'message' => 'پیام',
        ]);

        ContactMessage::create([
            ...$validated,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 1000),
            'is_read' => false,
        ]);

        $message = 'پیام شما با موفقیت ثبت شد. کارشناسان اتاق اصناف پس از بررسی با شما در ارتباط خواهند بود.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
            ], 201);
        }

        return redirect()->route('contact.create')->with('success', $message);
    }

    private function failedSubmissionResponse(Request $request): RedirectResponse|JsonResponse
    {
        $message = 'ارسال پیام امکان‌پذیر نشد. لطفاً صفحه را تازه‌سازی کرده و دوباره تلاش کنید.';

        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 422);
        }

        return back()->withErrors(['form' => $message])->withInput($request->except('website'));
    }
}
