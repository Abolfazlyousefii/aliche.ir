<?php

namespace Tests\Feature\Admin;

use App\Models\Advertisement;
use App\Models\AdvertisementPosition;
use App\Models\ChamberMember;
use App\Models\Commission;
use App\Models\ElectronicService;
use App\Models\Gallery;
use App\Models\Media;
use App\Models\System;
use App\Models\TourismPlace;
use App\Models\UnionType;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaControllersCrudRegressionTest extends TestCase
{
    use RefreshDatabase;

    private Media $media;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->signInAsSuperAdmin();
        Storage::disk('public')->put('media/selected.webp', 'image');
        $this->media = Media::query()->create([
            'file_name' => 'selected.webp',
            'original_name' => 'selected.webp',
            'path' => 'media/selected.webp',
            'disk' => 'public',
            'mime_type' => 'image/webp',
            'extension' => 'webp',
            'size' => 5,
            'width' => 100,
            'height' => 50,
        ]);
    }

    public function test_advertisement_crud_accepts_selected_media(): void
    {
        $position = AdvertisementPosition::query()->create(['title' => 'بنر', 'key' => 'hero', 'is_active' => true]);
        $payload = ['position_id' => $position->id, 'title' => 'تبلیغ', 'image_media_id' => $this->media->id, 'target' => '_self', 'starts_at' => now()->toDateTimeString(), 'is_active' => '1'];

        $this->post(route('admin.advertisements.store'), $payload)->assertRedirect();
        $item = Advertisement::query()->sole();
        $this->assertSame($this->media->path, $item->image);
        $this->put(route('admin.advertisements.update', $item), [...$payload, 'title' => 'تبلیغ ویرایش‌شده'])->assertRedirect();
        $this->assertSame('تبلیغ ویرایش‌شده', $item->fresh()->title);
        $this->delete(route('admin.advertisements.destroy', $item))->assertRedirect();
        $this->assertDatabaseMissing('advertisements', ['id' => $item->id]);
    }

    public function test_chamber_member_and_union_type_crud(): void
    {
        $memberPayload = ['first_name' => 'علی', 'last_name' => 'آزمون', 'position' => 'عضو', 'photo_media_id' => $this->media->id, 'sort_order' => 1, 'is_active' => '1'];
        $this->post(route('admin.chamber_members.store'), $memberPayload)->assertRedirect();
        $member = ChamberMember::query()->sole();
        $this->assertSame($this->media->path, $member->photo);
        $this->patch(route('admin.chamber_members.update', $member), [...$memberPayload, 'position' => 'رئیس'])->assertRedirect();
        $this->assertSame('رئیس', $member->fresh()->position);
        $this->delete(route('admin.chamber_members.destroy', $member))->assertRedirect();

        $typePayload = ['title' => 'خدماتی', 'slug' => 'service-type', 'image_media_id' => $this->media->id, 'sort_order' => 1, 'is_active' => '1'];
        $this->post(route('admin.union-types.store'), $typePayload)->assertRedirect();
        $type = UnionType::query()->where('slug', 'service-type')->firstOrFail();
        $this->assertSame($this->media->path, $type->image);
        $this->patch(route('admin.union-types.update', $type), [...$typePayload, 'title' => 'خدمات'])->assertRedirect();
        $this->assertSame('خدمات', $type->fresh()->title);
        $this->delete(route('admin.union-types.destroy', $type))->assertRedirect();
    }

    public function test_commission_gallery_system_and_tourism_crud(): void
    {
        $commissionPayload = ['title' => 'کمیسیون', 'slug' => 'commission-test', 'image_media_id' => $this->media->id, 'status' => 'draft', 'sort_order' => 0, 'is_active' => '1'];
        $this->post(route('admin.commissions.store'), $commissionPayload)->assertRedirect();
        $commission = Commission::query()->sole();
        $this->assertSame($this->media->path, $commission->image);
        $this->put(route('admin.commissions.update', $commission), [...$commissionPayload, 'title' => 'کمیسیون جدید'])->assertRedirect();
        $this->delete(route('admin.commissions.destroy', $commission))->assertRedirect();

        $galleryPayload = ['title' => 'گالری', 'slug' => 'gallery-test', 'cover_image_media_id' => $this->media->id, 'gallery_media_ids' => [$this->media->id], 'display_location' => 'both', 'status' => 'draft', 'sort_order' => 0, 'is_active' => '1'];
        $this->post(route('admin.galleries.store'), $galleryPayload)->assertRedirect();
        $gallery = Gallery::query()->sole();
        $this->assertSame($this->media->path, $gallery->cover_image);
        $this->assertSame($this->media->path, $gallery->images()->value('image'));
        $this->put(route('admin.galleries.update', $gallery), [...$galleryPayload, 'title' => 'گالری جدید'])->assertRedirect();
        $this->delete(route('admin.galleries.destroy', $gallery))->assertRedirect();

        $systemPayload = ['title' => 'سامانه', 'slug' => 'system-test', 'image_media_id' => $this->media->id, 'target' => '_self', 'status' => 'draft', 'sort_order' => 0, 'is_active' => '1'];
        $this->post(route('admin.systems.store'), $systemPayload)->assertRedirect();
        $system = System::query()->sole();
        $this->assertSame($this->media->path, $system->image);
        $this->put(route('admin.systems.update', $system), [...$systemPayload, 'title' => 'سامانه جدید'])->assertRedirect();
        $this->delete(route('admin.systems.destroy', $system))->assertRedirect();

        $tourismPayload = ['title' => 'گردشگری', 'slug' => 'tourism-test', 'featured_image_media_id' => $this->media->id, 'gallery_images_media_ids' => [$this->media->id], 'tourism_type' => 'nature', 'status' => 'draft', 'sort_order' => 0, 'is_active' => '1'];
        $this->post(route('admin.tourism.store'), $tourismPayload)->assertRedirect();
        $tourism = TourismPlace::query()->sole();
        $this->assertSame($this->media->path, $tourism->featured_image);
        $this->assertSame($this->media->path, $tourism->gallery[0]['path']);
        $this->put(route('admin.tourism.update', $tourism), [...$tourismPayload, 'title' => 'گردشگری جدید'])->assertRedirect();
        $this->delete(route('admin.tourism.destroy', $tourism))->assertRedirect();
    }

    public function test_video_and_electronic_service_crud_accept_selected_media(): void
    {
        $videoPayload = ['title' => 'ویدیو', 'slug' => 'video-test', 'cover_image_media_id' => $this->media->id, 'video_type' => 'aparat', 'aparat_url' => 'https://www.aparat.com/v/test', 'status' => 'draft', 'sort_order' => 0, 'is_active' => '1'];
        $this->post(route('admin.videos.store'), $videoPayload)->assertRedirect();
        $video = Video::query()->sole();
        $this->assertSame($this->media->path, $video->cover_image);
        $this->put(route('admin.videos.update', $video), [...$videoPayload, 'title' => 'ویدیو جدید'])->assertRedirect();
        $this->delete(route('admin.videos.destroy', $video))->assertRedirect();

        $servicePayload = ['title' => 'خدمت', 'slug' => 'service-test', 'image_media_id' => $this->media->id, 'link_type' => 'none', 'target' => '_self', 'status' => 'draft', 'sort_order' => 0, 'is_active' => '1'];
        $this->post(route('admin.electronic_services.store'), $servicePayload)->assertRedirect();
        $service = ElectronicService::query()->sole();
        $this->assertSame($this->media->path, $service->image);
        $this->put(route('admin.electronic_services.update', $service), [...$servicePayload, 'title' => 'خدمت جدید'])->assertRedirect();
        $this->delete(route('admin.electronic_services.destroy', $service))->assertRedirect();
    }

    public function test_header_footer_site_settings_and_rich_text_upload(): void
    {
        $this->put(route('admin.header_settings.update'), ['header_logo_media_id' => $this->media->id, 'top_date_enabled' => '1'])->assertRedirect();
        $this->put(route('admin.footer_settings.update'), ['footer_logo_media_id' => $this->media->id])->assertRedirect();
        $this->put(route('admin.settings.update'), ['site_logo_media_id' => $this->media->id, 'site_title' => 'سایت آزمون'])->assertRedirect();

        $this->postJson(route('admin.rich_text.upload'), [
            'file' => UploadedFile::fake()->image('editor.jpg', 320, 180),
            'type' => 'image',
        ])->assertOk()->assertJsonStructure(['location', 'path', 'name']);
    }
}
