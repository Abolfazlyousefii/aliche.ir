<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactMessageController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $readStatus = (string) $request->query('read_status', '');

        $messages = ContactMessage::query()
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('full_name', 'like', "%{$search}%")
                ->orWhere('mobile', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('subject', 'like', "%{$search}%")
                ->orWhere('message', 'like', "%{$search}%")))
            ->when($readStatus === 'read', fn ($query) => $query->where('is_read', true))
            ->when($readStatus === 'unread', fn ($query) => $query->where('is_read', false))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.contact_messages.index', compact('messages', 'search', 'readStatus'));
    }

    public function show(ContactMessage $contactMessage): View
    {
        $contactMessage->markAsRead();

        return view('admin.contact_messages.show', compact('contactMessage'));
    }

    public function markAsRead(ContactMessage $contactMessage): RedirectResponse
    {
        $contactMessage->markAsRead();

        return back()->with('success', 'پیام به عنوان خوانده‌شده علامت‌گذاری شد.');
    }

    public function destroy(ContactMessage $contactMessage): RedirectResponse
    {
        $contactMessage->delete();

        return redirect()->route('admin.contact_messages.index')->with('success', 'پیام ارتباطی با موفقیت حذف شد.');
    }
}
