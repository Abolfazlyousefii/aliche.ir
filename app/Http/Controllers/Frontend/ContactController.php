<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function create(SettingService $settings): View
    {
        return view('frontend.contact.create', compact('settings'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'mobile' => ['required', 'string', 'max:20', 'regex:/^[0-9۰-۹+\-\s()]{8,20}$/u'],
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

        return redirect()->route('contact.create')->with('success', 'پیام شما با موفقیت ثبت شد. همکاران ما در اولین فرصت آن را بررسی می‌کنند.');
    }
}
