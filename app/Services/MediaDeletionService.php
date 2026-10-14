<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Support\Facades\DB;
use Throwable;

class MediaDeletionService
{
    /**
     * Delete only locally owned media which are not referenced by any content.
     *
     * @return array{id:int,status:string,message:string}
     */
    public function delete(int $id): array
    {
        try {
            return DB::transaction(function () use ($id): array {
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

                if ($medium->inUse()) {
                    return $this->result($id, 'blocked', 'این تصویر در بخشی از سایت استفاده شده است.');
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
