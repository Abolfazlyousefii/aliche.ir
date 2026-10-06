<?php

namespace Tests\Feature;

use App\Models\TourismPlace;
use App\Services\SettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TourismNeshanMapTest extends TestCase
{
    use RefreshDatabase;

    public function test_destination_link_uses_neshan_without_requiring_an_api_key(): void
    {
        $place = $this->place([
            'latitude' => '36.8400000',
            'longitude' => '54.4400000',
        ]);

        $this->get(route('tourism.show', $place->slug))
            ->assertOk()
            ->assertSee('https://neshan.org/maps/share/36.8400000,54.4400000', false)
            ->assertSee('مسیریابی با نشان')
            ->assertDontSee('id="tourism-neshan-map"', false)
            ->assertDontSee('neshan-maplibre-sdk.umd.js', false);
    }

    public function test_configured_web_map_key_and_coordinates_render_the_map(): void
    {
        app(SettingService::class)->set('site.neshan_map_key', 'web.test-public-key', 'site');
        $place = $this->place([
            'latitude' => '36.8400000',
            'longitude' => '54.4400000',
        ]);

        $this->get(route('tourism.show', $place->slug))
            ->assertOk()
            ->assertSee('id="tourism-neshan-map"', false)
            ->assertSee('neshan-maplibre-sdk.css', false)
            ->assertSee('neshan-maplibre-sdk.umd.js', false)
            ->assertSee('web.test-public-key')
            ->assertSee('54.4400000, 36.8400000', false);
    }

    public function test_missing_coordinates_do_not_load_a_map_even_with_key(): void
    {
        app(SettingService::class)->set('site.neshan_map_key', 'web.test-public-key', 'site');
        $place = $this->place();

        $this->get(route('tourism.show', $place->slug))
            ->assertOk()
            ->assertDontSee('tourism-neshan-map', false)
            ->assertDontSee('web.test-public-key', false)
            ->assertDontSee('https://neshan.org/maps/share/', false);
    }

    public function test_admin_requires_both_coordinates_and_valid_ranges(): void
    {
        $this->signInAsSuperAdmin();

        $payload = [
            'title' => 'مقصد آزمایشی',
            'slug' => 'neshan-coordinates-invalid',
            'tourism_type' => 'nature',
            'status' => 'draft',
            'is_active' => '1',
            'latitude' => '36.8400000',
        ];

        $this->post(route('admin.tourism.store'), $payload)
            ->assertSessionHasErrors('longitude');

        $this->post(route('admin.tourism.store'), array_replace($payload, [
            'latitude' => '92',
            'longitude' => '54.44',
        ]))->assertSessionHasErrors('latitude');
    }

    public function test_admin_settings_keep_web_key_on_blank_and_remove_only_when_requested(): void
    {
        $this->signInAsSuperAdmin();
        $settings = app(SettingService::class);
        $settings->set('site.neshan_map_key', 'web.retained-key', 'site');

        $this->get(route('admin.settings.edit'))
            ->assertOk()
            ->assertSee('name="neshan_map_key"', false)
            ->assertDontSee('web.retained-key', false);

        $this->put(route('admin.settings.update'), [
            'site_title' => 'سایت تست',
            'neshan_map_key' => '',
        ])->assertSessionHasNoErrors();

        $this->assertSame('web.retained-key', $settings->get('site.neshan_map_key'));

        $this->put(route('admin.settings.update'), [
            'site_title' => 'سایت تست',
            'remove_neshan_map_key' => '1',
        ])->assertSessionHasNoErrors();

        $this->assertNull($settings->get('site.neshan_map_key'));
    }

    private function place(array $overrides = []): TourismPlace
    {
        return TourismPlace::query()->create(array_replace([
            'title' => 'مقصد گردشگری',
            'slug' => 'tourism-neshan-map-'.uniqid(),
            'tourism_type' => 'nature',
            'type' => 'nature',
            'status' => 'published',
            'published_at' => now()->subDay(),
            'sort_order' => 0,
            'is_active' => true,
        ], $overrides));
    }
}
