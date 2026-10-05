<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Media;
use App\Models\System;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemBackendRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_rejects_non_system_category(): void
    {
        $this->signInAsSuperAdmin();

        $videoCategory = Category::query()->create([
            'title' => 'دسته ویدیو',
            'slug' => 'video-category-for-system-test',
            'type' => 'video',
            'sort_order' => 0,
            'is_active' => true,
        ]);

        $this->post(route('admin.systems.store'), $this->payload([
            'slug' => 'system-with-wrong-category',
            'category_id' => $videoCategory->id,
        ]))
            ->assertSessionHasErrors('category_id');

        $this->assertDatabaseMissing('systems', ['slug' => 'system-with-wrong-category']);
    }

    public function test_admin_rejects_non_image_media_selection(): void
    {
        $this->signInAsSuperAdmin();

        $document = Media::query()->create([
            'file_name' => 'guide.pdf',
            'original_name' => 'guide.pdf',
            'path' => 'media/guide.pdf',
            'disk' => 'public',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'size' => 2048,
        ]);

        $this->post(route('admin.systems.store'), $this->payload([
            'slug' => 'system-with-non-image-media',
            'image_media_id' => $document->id,
        ]))
            ->assertSessionHasErrors('image_media_id');

        $this->assertDatabaseMissing('systems', ['slug' => 'system-with-non-image-media']);
    }

    public function test_admin_rejects_unsafe_public_link(): void
    {
        $this->signInAsSuperAdmin();

        $this->post(route('admin.systems.store'), $this->payload([
            'slug' => 'system-with-unsafe-link',
            'link' => 'javascript:alert(1)',
        ]))
            ->assertSessionHasErrors('link');

        $this->assertDatabaseMissing('systems', ['slug' => 'system-with-unsafe-link']);
    }

    public function test_valid_image_media_and_system_category_are_persisted(): void
    {
        $this->signInAsSuperAdmin();

        $category = Category::query()->create([
            'title' => 'سامانه‌های خدماتی',
            'slug' => 'service-system-category',
            'type' => 'system',
            'sort_order' => 0,
            'is_active' => true,
        ]);

        $image = Media::query()->create([
            'file_name' => 'system.jpg',
            'original_name' => 'system.jpg',
            'path' => 'media/system.jpg',
            'disk' => 'public',
            'mime_type' => 'image/jpeg',
            'extension' => 'jpg',
            'size' => 4096,
            'width' => 1200,
            'height' => 800,
        ]);

        $this->post(route('admin.systems.store'), $this->payload([
            'slug' => 'valid-system-backend',
            'category_id' => $category->id,
            'image_media_id' => $image->id,
            'link' => 'https://service.test/login',
        ]))
            ->assertSessionHasNoErrors();

        $system = System::query()->where('slug', 'valid-system-backend')->firstOrFail();

        $this->assertSame($category->id, $system->category_id);
        $this->assertSame($image->path, $system->image);
        $this->assertSame('https://service.test/login', $system->link);
    }

    private function payload(array $overrides = []): array
    {
        return array_replace([
            'title' => 'سامانه آزمایشی',
            'slug' => 'system-backend-regression',
            'description' => '<p>توضیحات کامل سامانه</p>',
            'short_description' => 'توضیح کوتاه سامانه',
            'icon' => '💻',
            'link' => '',
            'category_id' => null,
            'target' => '_blank',
            'status' => 'draft',
            'published_at' => '',
            'rejected_reason' => '',
            'sort_order' => 0,
            'is_active' => '1',
        ], $overrides);
    }
}
