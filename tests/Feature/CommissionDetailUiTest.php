<?php

namespace Tests\Feature;

use App\Models\Commission;
use App\Models\CommissionSession;
use App\Models\CommissionTask;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommissionDetailUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_detail_shows_dynamic_content_and_only_published_active_items(): void
    {
        $commission = $this->commission([
            'slug' => 'commission-detail-complete',
            'description' => '<p>توضیحات واقعی کمیسیون</p>',
            'image' => 'commissions/detail-photo.jpg',
            'members' => [['name' => 'عضو کمیسیون'], ['name' => '']],
            'attachments' => [['name' => 'فایل معرفی', 'path' => 'commissions/intro.pdf']],
        ]);

        CommissionTask::query()->create([
            'commission_id' => $commission->id,
            'title' => 'وظیفه فعال',
            'description' => '<p>توضیح وظیفه</p>',
            'is_active' => true,
            'sort_order' => 1,
        ]);
        CommissionTask::query()->create([
            'commission_id' => $commission->id,
            'title' => 'وظیفه غیرفعال',
            'is_active' => false,
            'sort_order' => 2,
        ]);
        CommissionSession::query()->create([
            'commission_id' => $commission->id,
            'title' => 'جلسه منتشرشده',
            'status' => 'published',
            'published_at' => now()->subMinute(),
            'session_date' => now()->subDay(),
            'minutes_file' => 'commission-sessions/minutes/meeting.pdf',
            'attachments' => [['name' => 'پیوست جلسه', 'path' => 'commission-sessions/attachments/doc.pdf']],
            'images' => [['name' => 'تصویر جلسه', 'path' => 'commission-sessions/images/photo.jpg']],
            'is_active' => true,
        ]);
        CommissionSession::query()->create([
            'commission_id' => $commission->id,
            'title' => 'جلسه پیش‌نویس',
            'status' => 'draft',
            'is_active' => true,
        ]);
        CommissionSession::query()->create([
            'commission_id' => $commission->id,
            'title' => 'جلسه غیرفعال',
            'status' => 'published',
            'published_at' => now()->subMinute(),
            'is_active' => false,
        ]);
        CommissionSession::query()->create([
            'commission_id' => $commission->id,
            'title' => 'جلسه آینده',
            'status' => 'published',
            'published_at' => now()->addDay(),
            'is_active' => true,
        ]);

        $this->get(route('commissions.show', $commission->slug))
            ->assertOk()
            ->assertSee('commission-profile-v3', false)
            ->assertSee('commission-profile-v3__thumbnail', false)
            ->assertSee('commission-profile-v3__tasks', false)
            ->assertSee('commission-profile-v3__sessions', false)
            ->assertSee('توضیحات واقعی کمیسیون')
            ->assertSee('عضو کمیسیون')
            ->assertSee('وظیفه فعال')
            ->assertSee('جلسه منتشرشده')
            ->assertSee('دریافت صورتجلسه')
            ->assertSee('پیوست جلسه')
            ->assertSee('تصویر جلسه')
            ->assertSee('فایل معرفی')
            ->assertDontSee('وظیفه غیرفعال')
            ->assertDontSee('جلسه پیش‌نویس')
            ->assertDontSee('جلسه غیرفعال')
            ->assertDontSee('جلسه آینده')
            ->assertDontSee('commission-detail-cover', false);
    }

    public function test_empty_detail_has_compact_summary_without_empty_task_and_session_cards(): void
    {
        $commission = $this->commission([
            'slug' => 'commission-detail-empty',
            'description' => null,
            'image' => null,
            'members' => [],
            'attachments' => [],
        ]);

        $this->get(route('commissions.show', $commission->slug))
            ->assertOk()
            ->assertSee('commission-profile-v3__overview', false)
            ->assertSee('توضیحات این کمیسیون هنوز در پنل مدیریت تکمیل نشده است.')
            ->assertDontSee('commission-profile-v3__thumbnail', false)
            ->assertDontSee('commission-profile-v3__tasks', false)
            ->assertDontSee('commission-profile-v3__sessions', false)
            ->assertDontSee('commission-profile-v3__members', false)
            ->assertDontSee('commission-profile-v3__attachments', false);
    }

    public function test_unpublished_commission_remains_inaccessible(): void
    {
        $commission = $this->commission([
            'slug' => 'commission-detail-draft',
            'status' => 'draft',
        ]);

        $this->get(route('commissions.show', $commission->slug))->assertNotFound();
    }

    private function commission(array $overrides = []): Commission
    {
        return Commission::query()->create(array_replace([
            'title' => 'کمیسیون آزمون',
            'slug' => 'commission-'.uniqid(),
            'description' => '<p>معرفی کمیسیون</p>',
            'status' => 'published',
            'published_at' => now()->subMinute(),
            'sort_order' => 1,
            'is_active' => true,
        ], $overrides));
    }
}
