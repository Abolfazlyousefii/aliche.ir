<?php

namespace Tests\Feature;

use App\Models\GuildUnion;
use App\Models\UnionType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeUnionsDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_unions_are_displayed_on_home_page(): void
    {
        $unionType = UnionType::query()->firstOrCreate(
            ['slug' => GuildUnion::TYPE_SERVICE],
            [
                'title' => 'اتحادیه‌های خدماتی',
                'icon' => '🧰',
                'sort_order' => 10,
                'is_active' => true,
            ]
        );

        GuildUnion::query()->create([
            'name' => 'اتحادیه تست خدمات',
            'title' => 'اتحادیه تست خدمات',
            'slug' => 'test-service-union',
            'union_type' => GuildUnion::TYPE_SERVICE,
            'union_type_id' => $unionType->id,
            'short_description' => 'توضیح کوتاه اتحادیه تست',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('اتحادیه تست خدمات')
            ->assertDontSee('اتحادیه فعالی برای نمایش در صفحه اصلی ثبت نشده است.');
    }
}
