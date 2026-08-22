<?php

namespace Tests\Feature\Unions;

use App\Models\Category;
use App\Models\GuildUnion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsAdminPayloads;
use Tests\TestCase;

class UnionAdminRegressionTest extends TestCase
{
    use BuildsAdminPayloads;
    use RefreshDatabase;

    public function test_create_and_edit_forms_exclude_legacy_category_but_keep_union_type(): void
    {
        $this->signInAsSuperAdmin();
        $union = $this->union(['slug' => 'union-form-edit']);

        foreach ([route('admin.unions.create'), route('admin.unions.edit', $union)] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertDontSee('name="category_id"', false)
                ->assertSee('name="union_type_id"', false);
        }
    }

    public function test_update_without_category_id_preserves_legacy_category_value(): void
    {
        $this->signInAsSuperAdmin();
        $category = Category::query()->create([
            'title' => 'دسته قدیمی اتحادیه',
            'slug' => 'legacy-union-category',
            'type' => 'union',
            'is_active' => true,
        ]);
        $union = $this->union([
            'slug' => 'legacy-category-union',
            'category_id' => $category->id,
        ]);

        $this->put(route('admin.unions.update', $union), $this->unionPayload([
            'title' => 'اتحادیه ویرایش‌شده',
            'slug' => $union->slug,
        ]))->assertSessionHasNoErrors();

        $this->assertSame($category->id, $union->refresh()->category_id);
    }

    public function test_manager_fields_are_persisted_on_store_and_update(): void
    {
        $this->signInAsSuperAdmin();

        $this->post(route('admin.unions.store'), $this->unionPayload([
            'title' => 'اتحادیه مدیر پویا',
            'slug' => 'dynamic-manager-union',
            'manager_name' => 'مدیر نخست',
            'manager_position' => 'رئیس اتحادیه',
            'manager_description' => 'معرفی کوتاه مدیر نخست',
        ]))->assertSessionHasNoErrors();

        $union = GuildUnion::query()->where('slug', 'dynamic-manager-union')->firstOrFail();
        $this->assertSame('مدیر نخست', $union->manager_name);
        $this->assertSame('رئیس اتحادیه', $union->manager_position);
        $this->assertSame('معرفی کوتاه مدیر نخست', $union->manager_description);

        $this->put(route('admin.unions.update', $union), $this->unionPayload([
            'title' => $union->title,
            'slug' => $union->slug,
            'manager_name' => 'مدیر دوم',
            'manager_position' => 'سرپرست اتحادیه',
            'manager_description' => 'معرفی به‌روزشده مدیر دوم',
        ]))->assertSessionHasNoErrors();

        $union->refresh();
        $this->assertSame('مدیر دوم', $union->manager_name);
        $this->assertSame('سرپرست اتحادیه', $union->manager_position);
        $this->assertSame('معرفی به‌روزشده مدیر دوم', $union->manager_description);
    }

    private function union(array $overrides = []): GuildUnion
    {
        return GuildUnion::query()->create(array_replace([
            'name' => 'اتحادیه تست مدیریت',
            'title' => 'اتحادیه تست مدیریت',
            'slug' => 'admin-union-'.uniqid(),
            'is_active' => true,
        ], $overrides));
    }
}
