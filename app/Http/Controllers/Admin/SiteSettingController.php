<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Concerns\SelectsMedia;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SiteSettingController extends Controller
{
    use SelectsMedia;
    public function edit(SettingService $settings): View
    {
        return view('admin.settings.site', ['settings' => $settings]);
    }

    public function update(Request $request, SettingService $settings): RedirectResponse
    {
        $validated = $request->validate([
            'site_title' => ['nullable', 'string', 'max:255'],
            'site_description' => ['nullable', 'string'],
            'site_logo' => ['nullable', 'image', 'max:4096'],
            'site_favicon' => ['nullable', 'image', 'max:1024'],
            'default_meta_title' => ['nullable', 'string', 'max:255'],
            'default_meta_description' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'map_url' => ['nullable', 'url', 'max:500'],
            'social_links_items' => ['nullable', 'array'],
            'social_links_items.*.title' => ['nullable', 'string', 'max:100'],
            'social_links_items.*.url' => ['nullable', 'url', 'max:500'],
            'social_links_items.*.icon' => ['nullable', 'string', 'max:50'],
            'social_links_items.*.platform' => ['nullable', 'string', 'max:50'],
        ]);

        $validated = $this->sanitizeRichTextFields($validated, ['site_description']);

        foreach (['site_logo', 'site_favicon'] as $field) {
            if ($path = $this->uploadedOrSelectedImage($request, $field, 'settings/site')) {
                if ($old = $settings->get('site.'.$field)) {
                    Storage::disk('public')->delete($old);
                }
                $validated[$field] = $path;
            } else {
                unset($validated[$field]);
            }
        }

        $validated['social_links'] = $this->socialItems($validated['social_links_items'] ?? []);
        unset($validated['social_links_items']);
        $settings->setMany($validated, 'site');

        return back()->with('success', 'تنظیمات سایت ذخیره شد.');
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
}
