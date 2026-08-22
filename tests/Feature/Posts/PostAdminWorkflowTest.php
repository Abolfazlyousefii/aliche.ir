<?php

namespace Tests\Feature\Posts;

use App\Models\GuildUnion;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsAdminPayloads;
use Tests\TestCase;

class PostAdminWorkflowTest extends TestCase
{
    use BuildsAdminPayloads;
    use RefreshDatabase;

    public function test_store_normalizes_supported_latin_and_persian_slugs(): void
    {
        $this->signInAsSuperAdmin();

        foreach ([
            ['gold-market-news', 'gold-market-news'],
            ['Gold Market News', 'gold-market-news-2'],
            ['gold_market_news', 'gold-market-news-3'],
            ['خبر مهم گرگان', 'خبر-مهم-گرگان'],
            ['news--test', 'news-test'],
        ] as $index => [$input, $expected]) {
            $this->post(route('admin.posts.store'), $this->postPayload([
                'title' => 'مطلب اسلاگ '.$index,
                'slug' => $input,
            ]))->assertSessionHasNoErrors();

            $this->assertDatabaseHas('posts', ['slug' => $expected]);
        }
    }

    public function test_all_official_types_are_accepted_and_unknown_or_legacy_types_are_rejected_on_create(): void
    {
        $this->signInAsSuperAdmin();

        foreach (Post::TYPES as $index => $type) {
            $this->post(route('admin.posts.store'), $this->postPayload([
                'title' => 'نوع رسمی '.$type,
                'slug' => 'official-type-'.$index,
                'type' => $type,
            ]))->assertSessionHasNoErrors();
        }

        $this->assertSame(Post::TYPES, Post::query()->orderBy('id')->pluck('type')->all());

        foreach (['foobar', 'article', 'announcement'] as $type) {
            $this->from(route('admin.posts.create'))
                ->post(route('admin.posts.store'), $this->postPayload([
                    'slug' => 'rejected-'.$type,
                    'type' => $type,
                ]))
                ->assertRedirect(route('admin.posts.create'))
                ->assertSessionHasErrors('type');
        }
    }

    public function test_legacy_type_can_be_preserved_during_edit_but_is_not_silently_converted(): void
    {
        $this->signInAsSuperAdmin();
        $post = Post::query()->create([
            'title' => 'مطلب قدیمی',
            'slug' => 'legacy-article',
            'type' => 'article',
            'status' => 'draft',
            'is_active' => true,
        ]);

        $this->put(route('admin.posts.update', $post), $this->postPayload([
            'title' => 'عنوان ویرایش‌شده',
            'slug' => $post->slug,
            'type' => 'article',
        ]))->assertSessionHasNoErrors();

        $post->refresh();
        $this->assertSame('article', $post->type);
        $this->assertSame('عنوان ویرایش‌شده', $post->title);
    }

    public function test_post_create_selector_contains_active_and_inactive_unions(): void
    {
        $this->signInAsSuperAdmin();
        $active = $this->union('اتحادیه فعال تست', 'active-selector-union', true);
        $inactive = $this->union('اتحادیه غیرفعال تست', 'inactive-selector-union', false);

        $this->get(route('admin.posts.create'))
            ->assertOk()
            ->assertSee($active->display_title)
            ->assertSee($inactive->display_title)
            ->assertSee('غیرفعال');
    }

    public function test_existing_inactive_union_is_valid_for_post_but_missing_union_is_rejected(): void
    {
        $this->signInAsSuperAdmin();
        $inactive = $this->union('اتحادیه غیرفعال قابل انتخاب', 'inactive-valid-union', false);

        $this->post(route('admin.posts.store'), $this->postPayload([
            'slug' => 'inactive-union-post',
            'union_id' => $inactive->id,
        ]))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('posts', ['slug' => 'inactive-union-post', 'union_id' => $inactive->id]);

        $this->from(route('admin.posts.create'))
            ->post(route('admin.posts.store'), $this->postPayload([
                'slug' => 'missing-union-post',
                'union_id' => 999999,
            ]))
            ->assertRedirect(route('admin.posts.create'))
            ->assertSessionHasErrors('union_id');
    }

    private function union(string $title, string $slug, bool $active): GuildUnion
    {
        return GuildUnion::query()->create([
            'name' => $title,
            'title' => $title,
            'slug' => $slug,
            'is_active' => $active,
        ]);
    }
}
