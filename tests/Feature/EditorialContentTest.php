<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EditorialContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_editorial_scope_contains_all_official_types_and_excludes_legacy_types(): void
    {
        foreach (array_merge(Post::TYPES, Post::LEGACY_TYPES) as $type) {
            Post::query()->create([
                'title' => $type,
                'slug' => 'editorial-'.$type,
                'type' => $type,
                'status' => 'published',
                'published_at' => now(),
                'is_active' => true,
            ]);
        }

        $this->assertSame(Post::TYPES, Post::query()->editorial()->orderBy('id')->pluck('type')->all());
    }

    public function test_official_type_labels_match_the_admin_contract(): void
    {
        $this->assertSame([
            'news' => 'خبر',
            'interview' => 'گفتگو',
            'report' => 'گزارش',
            'statement' => 'بیانیه',
            'note' => 'یادداشت',
            'photo_report' => 'گزارش تصویری',
            'video' => 'ویدئو',
        ], array_intersect_key(Post::typeLabels(), array_flip(Post::TYPES)));
    }
}
