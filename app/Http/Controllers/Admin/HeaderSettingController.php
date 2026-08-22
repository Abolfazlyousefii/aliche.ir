<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\SelectsMedia;
use App\Http\Controllers\Controller;
use App\Rules\SafeImageUpload;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class HeaderSettingController extends Controller
{
    use SelectsMedia;

    public function edit(SettingService $settings): View
    {
        return view('admin.settings.header', ['settings' => $settings]);
    }

    public function update(Request $request, SettingService $settings): RedirectResponse
    {
        $validated = $request->validate([
            'desktop_logo' => ['nullable', 'bail', 'file', new SafeImageUpload, 'max:'.config('media.max_upload_kilobytes', 5120)],
            'mobile_logo' => ['nullable', 'bail', 'file', new SafeImageUpload, 'max:'.config('media.max_upload_kilobytes', 5120)],
            'header_logo' => ['nullable', 'bail', 'file', new SafeImageUpload, 'max:'.config('media.max_upload_kilobytes', 5120)],
            'top_text' => ['nullable', 'string', 'max:255'],
            'top_date_enabled' => ['required', 'in:0,1'],
            'contact_button_text' => ['nullable', 'string', 'max:255'],
            'contact_button_link' => ['nullable', 'string', 'max:500'],
            'header_buttons' => ['nullable', 'json'],
        ]);

        foreach (['desktop_logo', 'mobile_logo', 'header_logo'] as $field) {
            if ($path = $this->uploadedOrSelectedImage($request, $field, 'settings/header')) {
                if ($old = $settings->get('header.'.$field)) {
                    Storage::disk('public')->delete($old);
                }
                $validated[$field] = $path;
            } else {
                unset($validated[$field]);
            }
        }

        $validated['top_date_enabled'] = (bool) $validated['top_date_enabled'];
        $validated['header_buttons'] = $this->jsonArray($validated['header_buttons'] ?? null);

        $settings->setMany($validated, 'header');

        return back()->with('success', 'تنظیمات هدر ذخیره شد.');
    }

    private function jsonArray(?string $value): array
    {
        if (! $value) {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? collect($decoded)->map(fn ($button) => [
            'title' => (string) ($button['title'] ?? ''),
            'url' => (string) ($button['url'] ?? '#'),
            'icon' => (string) ($button['icon'] ?? ''),
            'target' => in_array(($button['target'] ?? '_self'), ['_self', '_blank'], true) ? $button['target'] : '_self',
            'is_active' => filter_var($button['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
        ])->filter(fn ($button) => filled($button['title']) && filled($button['url']))->values()->all() : [];
    }
}
