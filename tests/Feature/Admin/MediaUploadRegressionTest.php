<?php

namespace Tests\Feature\Admin;

use App\Models\Media;
use App\Models\Permission;
use App\Models\Post;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaUploadRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_supported_formats_are_accepted_by_the_real_http_endpoint(): void
    {
        Storage::fake('public');
        $this->signInAsSuperAdmin();

        $files = [
            UploadedFile::fake()->image('photo.jpg', 640, 360),
            UploadedFile::fake()->image('photo.jpeg', 640, 360),
            UploadedFile::fake()->image('photo.png', 640, 360),
            $this->webp('photo.webp', 640, 360),
            UploadedFile::fake()->createWithContent('animation.gif', base64_decode('R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==')),
        ];

        foreach ($files as $file) {
            $this->postJson(route('admin.media.store'), ['files' => [$file]])
                ->assertCreated()->assertJsonCount(1, 'items');
        }

        $this->assertDatabaseCount('media', 5);
        foreach (Media::all() as $medium) {
            Storage::disk('public')->assertExists($medium->path);
            $this->assertNotNull(getimagesize(Storage::disk('public')->path($medium->path)));
            $this->assertStringStartsWith('/storage/', $medium->url);
        }
    }

    public function test_malicious_mismatched_and_oversized_uploads_leave_no_artifacts(): void
    {
        Storage::fake('public');
        $this->signInAsSuperAdmin();

        foreach (['shell.php', 'shell.php.jpg', 'malware.jpg'] as $name) {
            $this->postJson(route('admin.media.store'), [
                'files' => [UploadedFile::fake()->createWithContent($name, '<?php echo "owned";')],
            ])->assertUnprocessable();
        }

        $this->postJson(route('admin.media.store'), [
            'files' => [UploadedFile::fake()->create('large.jpg', 5121, 'image/jpeg')],
        ])->assertUnprocessable();

        $this->assertDatabaseCount('media', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_large_image_is_resized_without_ratio_loss_and_small_image_is_not_upscaled(): void
    {
        Storage::fake('public');
        $this->signInAsSuperAdmin();

        $this->postJson(route('admin.media.store'), [
            'files' => [UploadedFile::fake()->image('large.jpg', 3000, 2000)],
        ])->assertCreated();
        $large = Media::firstOrFail();
        $this->assertSame([1920, 1280], [$large->width, $large->height]);

        $this->postJson(route('admin.media.store'), [
            'files' => [UploadedFile::fake()->image('small.png', 200, 100)],
        ])->assertCreated();
        $small = Media::latest('id')->firstOrFail();
        $this->assertSame([200, 100], [$small->width, $small->height]);
    }

    public function test_duplicate_and_unicode_names_get_safe_unique_paths(): void
    {
        Storage::fake('public');
        $this->signInAsSuperAdmin();

        foreach (['تصویر آزمون.jpg', 'تصویر آزمون.jpg'] as $name) {
            $this->postJson(route('admin.media.store'), [
                'files' => [UploadedFile::fake()->image($name, 320, 180)],
            ])->assertCreated();
        }

        [$first, $second] = Media::orderBy('id')->get()->all();
        $this->assertSame('تصویر آزمون.jpg', $first->original_name);
        $this->assertNotSame($first->path, $second->path);
        $this->assertMatchesRegularExpression('#^media/\d{4}/\d{2}/[0-9a-f-]+\.webp$#', $first->path);
    }

    public function test_authentication_authorization_csrf_markup_and_path_traversal_are_hardened(): void
    {
        Storage::fake('public');
        $file = fn () => UploadedFile::fake()->image('../../escape.jpg', 100, 50);

        $this->post(route('admin.media.store'), ['files' => [$file()]])->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create(['is_active' => true]));
        $this->postJson(route('admin.media.store'), ['files' => [$file()]])->assertForbidden();

        $user = $this->userWithPermission('media.upload');
        $this->actingAs($user)->postJson(route('admin.media.store'), ['files' => [$file()]])->assertCreated();
        $medium = Media::firstOrFail();
        $this->assertStringNotContainsString('..', $medium->path);
        $this->assertStringStartsWith('media/', $medium->path);

        $this->actingAs($this->userWithPermission('media.view'))
            ->get(route('admin.media.index'))->assertOk()->assertSee('name="_token"', false);
    }

    public function test_metadata_is_html_escaped_and_edit_requires_its_own_permission(): void
    {
        Storage::fake('public');
        $this->signInAsSuperAdmin();
        $this->postJson(route('admin.media.store'), ['files' => [UploadedFile::fake()->image('x.jpg')]])->assertCreated();
        $medium = Media::firstOrFail();

        $this->actingAs($this->userWithPermission('media.upload'))
            ->put(route('admin.media.update', $medium), ['title' => '<script>alert(1)</script>'])->assertForbidden();

        $this->actingAs($this->userWithPermission('media.edit'))
            ->put(route('admin.media.update', $medium), ['title' => '<script>alert(1)</script>'])->assertRedirect();

        $this->actingAs($this->userWithPermission('media.view'))
            ->get(route('admin.media.index'))
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_unused_media_is_deleted_with_variants_but_referenced_media_is_protected(): void
    {
        Storage::fake('public');
        $this->signInAsSuperAdmin();
        $this->postJson(route('admin.media.store'), ['files' => [UploadedFile::fake()->image('used.jpg', 1200, 600)]])->assertCreated();
        $used = Media::firstOrFail();
        Post::query()->create([
            'title' => 'Referenced post',
            'slug' => 'referenced-post',
            'featured_media_id' => $used->id,
        ]);

        $this->delete(route('admin.media.destroy', $used))->assertRedirect()->assertSessionHas('error');
        $this->assertDatabaseHas('media', ['id' => $used->id]);
        Storage::disk('public')->assertExists($used->path);

        $this->postJson(route('admin.media.store'), ['files' => [UploadedFile::fake()->image('unused.jpg', 1200, 600)]])->assertCreated();
        $unused = Media::latest('id')->firstOrFail();
        $paths = app(\App\Services\MediaLibraryService::class)->pathsFor($unused->path);
        $this->delete(route('admin.media.destroy', $unused))->assertRedirect()->assertSessionHas('success');
        $this->assertDatabaseMissing('media', ['id' => $unused->id]);
        foreach ($paths as $path) {
            Storage::disk('public')->assertMissing($path);
        }
    }

    private function userWithPermission(string $permissionName): User
    {
        $permission = Permission::query()->firstOrCreate(
            ['name' => $permissionName],
            ['label' => $permissionName, 'group' => 'media']
        );
        $role = Role::query()->create(['name' => 'role-'.str()->uuid(), 'label' => 'Test']);
        $role->permissions()->attach($permission);
        $user = User::factory()->create(['is_active' => true]);
        $user->roles()->attach($role);

        return $user;
    }

    private function webp(string $name, int $width, int $height): UploadedFile
    {
        $image = imagecreatetruecolor($width, $height);
        ob_start();
        imagewebp($image, null, 82);
        $contents = ob_get_clean();
        imagedestroy($image);

        return UploadedFile::fake()->createWithContent($name, $contents);
    }
}
