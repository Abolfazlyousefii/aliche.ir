<?php

namespace App\Services;

use App\Models\Media;
use App\Models\Gallery;
use App\Models\GalleryImage;
use Illuminate\Support\Facades\DB;
use Throwable;

class MediaDeletionService
{
    /**
     * Delete only locally owned media. When scoped to an editable gallery,
     * detach that gallery's own references after checking all other uses.
     *
     * @return array{id:int,status:string,message:string}
     */
    public function delete(int $id, ?int $galleryId = null): array
    {
        try {
            return DB::transaction(function () use ($id, $galleryId): array {
                $medium = Media::query()->lockForUpdate()->find($id);

                if (! $medium) {
                    return $this->result($id, 'missing', 'این تصویر دیگر در کتابخانه موجود نیست.');
                }

                // Imported URLs and shared theme assets are not owned by this
                // library; never delete their records/files via bulk cleanup.
                if (! $medium->path || $medium->isExternalOrAsset()) {
                    return $this->result($id, 'blocked', 'این تصویر یک فایل مشترک یا خارجی است و قابل حذف نیست.');
                }

                // Two media entries can point to the same physical file.
                // Deleting either entry would leave the other with a broken URL.
                if (Media::query()->where('path', $medium->path)->whereKeyNot($id)->exists()) {
                    return $this->result($id, 'blocked', 'این فایل با یک رکورد رسانه دیگر مشترک است.');
                }

                $gallery = $galleryId ? Gallery::query()->lockForUpdate()->find($galleryId) : null;
                if ($galleryId && ! $gallery) {
                    return $this->result($id, 'blocked', 'گالری موردنظر وجود ندارد.');
                }

                // We only detach precise path matches. Legacy URLs that merely
                // contain the stored path are intentionally blocked, so neither
                // the gallery nor another content area retains a broken URL.
                if ($gallery && (
                    GalleryImage::query()->where('gallery_id', $gallery->id)
                        ->where('image', 'like', '%'.$medium->path.'%')
                        ->where('image', '!=', $medium->path)->exists()
                    || (str_contains((string) $gallery->cover_image, $medium->path)
                        && $gallery->cover_image !== $medium->path)
                )) {
                    return $this->result($id, 'blocked', 'ارجاع این تصویر در گالری به شکل قدیمی ذخیره شده و نیازمند اصلاح دستی است.');
                }

                if ($medium->inUse($gallery?->id)) {
                    return $this->result($id, 'blocked', 'این تصویر در بخش دیگری از سایت استفاده شده است.');
                }

                if ($gallery) {
                    GalleryImage::query()->where('gallery_id', $gallery->id)
                        ->where('image', $medium->path)->delete();

                    if ($gallery->cover_image === $medium->path) {
                        // Choose the first remaining gallery image, or clear
                        // the cover when no images remain.
                        $replacement = $gallery->images()->orderBy('sort_order')
                            ->orderBy('id')->value('image');
                        $gallery->update(['cover_image' => $replacement]);
                    }
                }

                $medium->delete();
                app(MediaLibraryService::class)->deleteFiles($medium);

                return $this->result($id, 'deleted', 'تصویر از کتابخانه حذف شد.');
            });
        } catch (Throwable $exception) {
            report($exception);

            return $this->result($id, 'failed', 'حذف تصویر با خطا مواجه شد؛ لطفاً دوباره تلاش کنید.');
        }
    }

    /** @return array{id:int,status:string,message:string} */
    private function result(int $id, string $status, string $message): array
    {
        return compact('id', 'status', 'message');
    }
}
