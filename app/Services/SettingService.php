<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;

class SettingService
{
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->all()[$key] ?? $default;
    }

    public function group(string $group): array
    {
        return collect($this->all())->filter(fn ($value, $key) => str_starts_with($key, $group.'.'))->all();
    }

    public function set(string $key, mixed $value, ?string $group = null): void
    {
        SiteSetting::updateOrCreate(['key' => $key], ['group' => $group, 'value' => $value]);
        Cache::forget('site_settings.all');
    }

    public function setMany(array $values, string $group): void
    {
        foreach ($values as $key => $value) {
            $this->set($group.'.'.$key, $value, $group);
        }
    }

    public function all(): array
    {
        return Cache::rememberForever('site_settings.all', fn () => SiteSetting::query()
            ->get(['key', 'value'])
            ->mapWithKeys(fn (SiteSetting $setting) => [$setting->key => $setting->value])
            ->all());
    }
}
