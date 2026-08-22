<?php

namespace Tests\Feature\Admin;

use App\Models\Media;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MediaPickerTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $role = Role::create(['name' => 'super-admin', 'label' => 'مدیرکل']);
        $user->roles()->attach($role);

        return $user;
    }

    private function image(array $attributes = []): Media
    {
        return Media::query()->create(array_merge([
            'file_name' => fake()->uuid().'.jpg',
            'original_name' => 'sample.jpg',
            'path' => 'media/'.fake()->uuid().'.jpg',
            'disk' => 'public',
            'mime_type' => 'image/jpeg',
            'extension' => 'jpg',
            'size' => 1024,
            'width' => 800,
            'height' => 600,
        ], $attributes));
    }

    public function test_picker_is_paginated_and_returns_only_requested_page(): void
    {
        $uploader = $this->admin();
        foreach (range(1, 30) as $number) {
            $this->image(['title' => "image {$number}", 'uploaded_by' => $uploader->id]);
        }

        $this->actingAs($uploader)
            ->getJson(route('admin.media.picker', ['page' => 1, 'per_page' => 24]))
            ->assertOk()
            ->assertJsonCount(24, 'data')
            ->assertJsonPath('current_page', 1)
            ->assertJsonPath('last_page', 2)
            ->assertJsonPath('total', 30)
            ->assertJsonStructure(['data' => [['id', 'url', 'thumbnail', 'title', 'alt', 'size', 'width', 'height', 'mime_type', 'uploaded_at', 'uploader']]]);
    }

    public function test_picker_searches_caption_and_filters_real_mime_type(): void
    {
        $admin = $this->admin();
        $this->image(['caption' => 'نشست ویژه گرگان', 'mime_type' => 'image/webp', 'extension' => 'webp']);
        $this->image(['caption' => 'نشست ویژه گرگان', 'mime_type' => 'image/png', 'extension' => 'png']);
        $this->image(['title' => 'unrelated', 'mime_type' => 'image/webp', 'extension' => 'webp']);

        $this->actingAs($admin)
            ->getJson(route('admin.media.picker', ['search' => 'گرگان', 'type' => 'webp']))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.mime_type', 'image/webp');
    }

    public function test_picker_sorts_by_file_size(): void
    {
        $admin = $this->admin();
        $this->image(['title' => 'small', 'size' => 10]);
        $this->image(['title' => 'large', 'size' => 5000]);

        $this->actingAs($admin)
            ->getJson(route('admin.media.picker', ['sort' => 'largest']))
            ->assertOk()
            ->assertJsonPath('data.0.title', 'large');
    }
}
