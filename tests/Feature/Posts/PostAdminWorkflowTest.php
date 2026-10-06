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

    public function test_draft_has_no_automatic_publication_date(): void
    {
        $this->travelTo(now()->startOfMinute());
        $this->signInAsSuperAdmin();

        $this->get(route('admin.posts.create'))
            ->assertOk()
            ->assertSee('تاریخ انتشار (شمسی)')
            ->assertSee('name="published_at" type="text" data-jalali-datepicker value=""', false);

        $this->post(route('admin.posts.store'), $this->postPayload([
            'slug' => 'news-draft-no-date',
        ]))->assertRedirect()->assertSessionHasNoErrors();

        $post = Post::query()->where('slug', 'news-draft-no-date')->firstOrFail();
        $this->assertSame('draft', $post->status);
        $this->assertNull($post->published_at);
        $this->get(route('posts.index'))->assertOk()->assertDontSee($post->title);
    }

    public function test_old_draft_published_directly_gets_actual_publication_time(): void
    {
        $this->travelTo(now()->startOfMinute());
        $this->signInAsSuperAdmin();

        $post = Post::query()->create([
            'title' => 'خبر قدیمی آماده انتشار',
            'slug' => 'old-draft-current-news',
            'type' => 'news',
            'status' => 'pending',
            'published_at' => now()->subDays(7),
            'is_active' => true,
        ]);

        $this->patch(route('admin.posts.publish', $post))
            ->assertRedirect();

        $post->refresh();
        $this->assertSame('published', $post->status);
        $this->assertSame(now()->format('Y-m-d H:i:s'), $post->published_at->format('Y-m-d H:i:s'));
        $this->get(route('posts.show', $post->slug))
            ->assertOk()
            ->assertSee(jalali_date($post->published_at));
    }

    public function test_scheduled_post_keeps_persian_future_date_until_visibility_time(): void
    {
        $this->travelTo(now()->startOfMinute());
        $this->signInAsSuperAdmin();
        $scheduledAt = now()->addDays(3)->startOfMinute();
        $jalaliDate = jalali_datetime($scheduledAt);

        $this->post(route('admin.posts.store'), $this->postPayload([
            'title' => 'خبر زمان‌بندی‌شده',
            'slug' => 'jalali-scheduled-post',
            'status' => 'pending',
            'published_at' => $jalaliDate,
        ]))->assertRedirect()->assertSessionHasNoErrors();

        $post = Post::query()->where('slug', 'jalali-scheduled-post')->firstOrFail();
        $this->assertSame($scheduledAt->format('Y-m-d H:i:s'), $post->published_at->format('Y-m-d H:i:s'));
        $this->get(route('admin.posts.edit', $post))->assertOk()->assertSee($jalaliDate);

        $this->patch(route('admin.posts.publish', $post))->assertRedirect();
        $post->refresh();
        $this->assertSame($scheduledAt->format('Y-m-d H:i:s'), $post->published_at->format('Y-m-d H:i:s'));
        $this->get(route('posts.index'))->assertOk()->assertDontSee($post->title);

        $this->travelTo($scheduledAt->copy()->addMinute());
        $this->get(route('posts.index'))->assertOk()->assertSee($post->title);
    }

    public function test_publishing_an_old_draft_by_editing_to_published_uses_today_when_date_empty(): void
    {
        $this->travelTo(now()->startOfMinute());
        $this->signInAsSuperAdmin();
        $post = Post::query()->create([
            'title' => 'خبر ویرایش‌شده',
            'slug' => 'edited-old-pending',
            'type' => 'news',
            'status' => 'pending',
            'published_at' => now()->subDays(5),
            'is_active' => true,
        ]);

        $this->get(route('admin.posts.edit', $post))
            ->assertOk()
            ->assertSee('name="published_at" type="text" data-jalali-datepicker value=""', false);

        $this->put(route('admin.posts.update', $post), $this->postPayload([
            'title' => $post->title,
            'slug' => $post->slug,
            'status' => 'published',
            'published_at' => '',
        ]))->assertRedirect()->assertSessionHasNoErrors();

        $post->refresh();
        $this->assertSame('published', $post->status);
        $this->assertSame(now()->format('Y-m-d H:i:s'), $post->published_at->format('Y-m-d H:i:s'));
    }

    public function test_explicit_jalali_backdate_is_honored_on_direct_publication(): void
    {
        $this->travelTo(now()->startOfMinute());
        $this->signInAsSuperAdmin();
        $chosenAt = now()->subDays(2);

        $this->post(route('admin.posts.store'), $this->postPayload([
            'slug' => 'manually-backdated-news',
            'status' => 'published',
            'published_at' => jalali_datetime($chosenAt),
        ]))->assertRedirect()->assertSessionHasNoErrors();

        $post = Post::query()->where('slug', 'manually-backdated-news')->firstOrFail();
        $this->assertSame($chosenAt->format('Y-m-d H:i:s'), $post->published_at->format('Y-m-d H:i:s'));
        $this->get(route('posts.show', $post->slug))->assertOk();
    }

    public function test_editing_published_news_keeps_its_existing_publication_date(): void
    {
        $this->travelTo(now()->startOfMinute());
        $this->signInAsSuperAdmin();
        $date = now()->subDays(12);
        $post = Post::query()->create([
            'title' => 'خبر منتشرشده',
            'slug' => 'edit-published-keep-date',
            'type' => 'news',
            'status' => 'published',
            'published_at' => $date,
            'is_active' => true,
        ]);

        $this->put(route('admin.posts.update', $post), $this->postPayload([
            'title' => 'خبر منتشرشده ویرایش‌شده',
            'slug' => $post->slug,
            'status' => 'published',
            'published_at' => '',
        ]))->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame($date->format('Y-m-d H:i:s'), $post->fresh()->published_at->format('Y-m-d H:i:s'));
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
