<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\Role;
use App\Models\Post;
use App\Models\SlugHistory;
use App\Models\User;
use App\Services\SlugService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PostPublishingMediaTest extends TestCase
{
    use RefreshDatabase;

    public function test_persian_slug_is_generated_and_deduplicated(): void
    {
        $service = app(SlugService::class);
        Post::create(['title'=>'عنوان خبر','slug'=>'عنوان-خبر','type'=>'news','status'=>'draft','is_active'=>true]);
        $this->assertSame('عنوان-خبر-2', $service->unique(Post::class, 'عنوان خبر'));
    }

    public function test_published_post_opens_and_draft_is_hidden(): void
    {
        $published = Post::create(['title'=>'خبر منتشرشده','slug'=>'خبر-منتشرشده','type'=>'news','status'=>'published','published_at'=>now(),'is_active'=>true]);
        $draft = Post::create(['title'=>'پیش نویس','slug'=>'پیش-نویس','type'=>'news','status'=>'draft','published_at'=>now(),'is_active'=>true]);
        $this->get(route('posts.show', $published->slug))->assertOk()->assertSee('خبر منتشرشده');
        $this->get(route('posts.show', $draft->slug))->assertNotFound();
    }

    public function test_old_slug_redirects_to_current_slug(): void
    {
        $post = Post::create(['title'=>'خبر','slug'=>'new-slug','type'=>'news','status'=>'published','published_at'=>now(),'is_active'=>true]);
        SlugHistory::create(['sluggable_type'=>Post::class,'sluggable_id'=>$post->id,'old_slug'=>'old-slug','new_slug'=>'new-slug']);
        $this->get('/news/old-slug')->assertRedirect(route('posts.show', 'new-slug'));
    }

    public function test_media_upload_and_used_media_is_not_deleted(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['is_active'=>true]);
        $role = Role::create(['name' => 'super-admin', 'label' => 'مدیرکل']);
        $user->roles()->attach($role);

        $this->actingAs($user)->post(route('admin.media.store'), ['files'=>[UploadedFile::fake()->image('a.jpg')]])->assertRedirect();
        $media = Media::first();
        Post::create(['title'=>'خبر','slug'=>'media-post','type'=>'news','status'=>'draft','is_active'=>true,'featured_media_id'=>$media->id]);
        $this->actingAs($user)->delete(route('admin.media.destroy', $media))->assertSessionHas('error');
        $this->assertDatabaseHas('media', ['id'=>$media->id]);
    }
}
