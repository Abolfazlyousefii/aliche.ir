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

class FooterSettingController extends Controller
{
    use SelectsMedia;

    public function edit(SettingService $settings): View
    {
        return view('admin.settings.footer', ['settings' => $settings]);
    }

    public function update(Request $request, SettingService $settings): RedirectResponse
    {
        $validated = $request->validate([
            'footer_logo' => ['nullable', 'bail', 'file', new SafeImageUpload, 'max:'.config('media.max_upload_kilobytes', 5120)],
            'footer_description' => ['nullable', 'string'],
            'copyright_text' => ['nullable', 'string', 'max:500'],
            'footer_columns' => ['nullable', 'json'],
            'footer_contact_info' => ['nullable', 'json'],
            'footer_social_links_items' => ['nullable', 'array'],
            'footer_social_links_items.*.title' => ['nullable', 'string', 'max:100'],
            'footer_social_links_items.*.url' => ['nullable', 'url', 'max:500'],
            'footer_social_links_items.*.icon' => ['nullable', 'string', 'max:50'],
            'footer_social_links_items.*.platform' => ['nullable', 'string', 'max:50'],
        ]);

        $validated = $this->sanitizeRichTextFields($validated, ['footer_description']);

        if ($footerLogo = $this->uploadedOrSelectedImage($request, 'footer_logo', 'settings/footer')) {
            if ($old = $settings->get('footer.footer_logo')) {
                Storage::disk('public')->delete($old);
            }
            $validated['footer_logo'] = $footerLogo;
        } else {
            unset($validated['footer_logo']);
        }

        foreach (['footer_columns', 'footer_contact_info'] as $field) {
            $validated[$field] = $this->jsonArray($validated[$field] ?? null);
        }

        $validated['footer_social_links'] = $this->socialItems($validated['footer_social_links_items'] ?? []);
        unset($validated['footer_social_links_items']);

        $settings->setMany($validated, 'footer');

        return back()->with('success', 'تنظیمات فوتر ذخیره شد.');
    }

    /** @param array<int, array<string, mixed>> $items */
    private function socialItems(array $items): array
    {
        $platforms = config('social_platforms', []);

        return collect($items)->map(function ($item) use ($platforms) {
            $platformKey = (string) ($item['platform'] ?? 'custom');
            $platform = $platforms[$platformKey] ?? $platforms['custom'] ?? ['title' => '', 'icon' => ''];

            return [
                'platform' => $platformKey,
                'title' => trim((string) ($item['title'] ?? $platform['title'] ?? '')),
                'url' => trim((string) ($item['url'] ?? '')),
                'icon' => trim((string) ($item['icon'] ?? $platform['icon'] ?? '')),
                'target' => '_blank',
            ];
        })->filter(fn ($item) => filled($item['title']) && filled($item['url']))->values()->all();
    }

    private function jsonArray(?string $value): array
    {
        if (! $value) {
            return [];
        }
        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }
}
