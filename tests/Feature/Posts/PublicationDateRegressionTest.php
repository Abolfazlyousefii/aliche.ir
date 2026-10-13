<?php

namespace Tests\Feature\Posts;

use App\Models\Post;
use App\Services\ContentApprovalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\Concerns\BuildsAdminPayloads;
use Tests\TestCase;

class PublicationDateRegressionTest extends TestCase
{
    use BuildsAdminPayloads;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(Carbon::parse('2026-10-12 12:00:00'));
        $this->signInAsSuperAdmin();
    }

    private function savePending(string $date = '۱۴۰۵/۰۶/۱۵ ۰۹:۲۰'): Post
    {
        $this->post(route('admin.posts.store'), $this->postPayload([
            'title' => 'دستگاه‌های گلستان؛ آزمون تاریخ انتشار',
            'slug' => 'publication-date-regression',
            'status' => 'pending',
            'published_at' => $date,
        ]))->assertRedirect()->assertSessionHasNoErrors();

        return Post::where('slug', 'publication-date-regression')->firstOrFail();
    }

    public function test_past_jalali_date_survives_direct_publish_and_public_display(): void
    {
        $post = $this->savePending();
        $chosen = $post->published_at->format('Y-m-d H:i:s');
        $this->assertSame('2026-09-06 09:20:00', $chosen);
        $this->patch(route('admin.posts.publish', $post))->assertRedirect();
        $this->assertSame($chosen, $post->fresh()->published_at->format('Y-m-d H:i:s'));
        $this->get(route('posts.show', $post->slug))->assertOk()->assertSee(jalali_date(Carbon::parse($chosen)));
    }

    public function test_past_date_survives_publication_from_moderation_queue(): void
    {
        $post = $this->savePending();
        $chosen = $post->published_at->format('Y-m-d H:i:s');
        $this->patch(route('admin.pending_approvals.publish', ['posts', $post->id]))->assertRedirect();
        $this->assertSame('published', $post->fresh()->status);
        $this->assertSame($chosen, $post->fresh()->published_at->format('Y-m-d H:i:s'));
    }

    public function test_edit_form_displays_saved_past_date(): void
    {
        $post = $this->savePending();
        $this->get(route('admin.posts.edit', $post))->assertOk()
            ->assertSee('name="published_at" type="text" data-jalali-datepicker value="'.jalali_input_datetime($post->published_at).'"', false);
    }

    public function test_content_edit_without_a_new_date_preserves_saved_date(): void
    {
        $post = $this->savePending();
        $chosen = $post->published_at->format('Y-m-d H:i:s');
        $payload = $this->postPayload(['title' => 'متن ویرایش‌شده', 'slug' => $post->slug, 'status' => 'pending']);
        unset($payload['published_at']);
        $this->put(route('admin.posts.update', $post), $payload)->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame($chosen, $post->fresh()->published_at?->format('Y-m-d H:i:s'));
    }

    public function test_date_can_be_changed_again_before_publication(): void
    {
        $post = $this->savePending();
        $this->put(route('admin.posts.update', $post), $this->postPayload([
            'slug' => $post->slug, 'status' => 'pending', 'published_at' => '۱۴۰۵/۰۶/۱۶ ۱۰:۳۰',
        ]))->assertRedirect()->assertSessionHasNoErrors();
        $this->patch(route('admin.posts.publish', $post))->assertRedirect();
        $this->assertSame('2026-09-07 10:30:00', $post->fresh()->published_at->format('Y-m-d H:i:s'));
    }

    public function test_missing_date_defaults_to_publish_time(): void
    {
        $post = $this->savePending('');
        $this->assertNull($post->published_at);
        $this->patch(route('admin.posts.publish', $post))->assertRedirect();
        $this->assertSame(now()->format('Y-m-d H:i:s'), $post->fresh()->published_at->format('Y-m-d H:i:s'));
    }

    public function test_future_schedule_stays_hidden_until_its_time(): void
    {
        $future = now()->addDays(3);
        $post = $this->savePending(jalali_input_datetime($future));
        $this->patch(route('admin.posts.publish', $post))->assertRedirect();
        $this->assertSame($future->format('Y-m-d H:i:s'), $post->fresh()->published_at->format('Y-m-d H:i:s'));
        $this->get(route('posts.show', $post->slug))->assertNotFound();
        $this->travelTo($future->copy()->addMinute());
        $this->get(route('posts.show', $post->slug))->assertOk();
    }

    public function test_republication_keeps_the_original_selected_date(): void
    {
        $post = $this->savePending();
        $chosen = $post->published_at->format('Y-m-d H:i:s');
        $post->update(['status' => 'published']);
        $this->patch(route('admin.posts.unpublish', $post))->assertRedirect();
        $this->patch(route('admin.posts.publish', $post))->assertRedirect();
        $this->assertSame($chosen, $post->fresh()->published_at->format('Y-m-d H:i:s'));
    }
}
