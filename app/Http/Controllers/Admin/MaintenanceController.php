<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Throwable;

class MaintenanceController extends Controller
{
    private const UNION_MEMBER_IMAGE_MIGRATION = 'database/migrations/2026_08_27_000001_add_image_to_union_members_table.php';
    private const UNION_MANAGER_PROFILE_MIGRATION = 'database/migrations/2026_08_22_000001_add_manager_profile_fields_to_unions_table.php';

    public function unionMemberImage(Request $request): View
    {
        $this->ensureSuperAdmin($request);

        return view('admin.maintenance.union-member-image', [
            'tableExists' => Schema::hasTable('union_members'),
            'imageColumnExists' => Schema::hasTable('union_members') && Schema::hasColumn('union_members', 'image'),
        ]);
    }

    public function runUnionMemberImage(Request $request): RedirectResponse
    {
        $this->ensureSuperAdmin($request);

        if (! Schema::hasTable('union_members')) {
            return back()->with('error', 'جدول union_members در دیتابیس پیدا نشد؛ عملیات انجام نشد.');
        }

        if (Schema::hasColumn('union_members', 'image')) {
            return back()->with('success', 'ستون image از قبل وجود دارد و نیازی به اجرای migration نیست.');
        }

        try {
            $exitCode = Artisan::call('migrate', [
                '--path' => self::UNION_MEMBER_IMAGE_MIGRATION,
                '--force' => true,
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', 'اجرای migration با خطا مواجه شد. جزئیات در لاگ Laravel ثبت شد.');
        }

        if ($exitCode !== 0 || ! Schema::hasColumn('union_members', 'image')) {
            return back()->with('error', 'migration اجرا شد اما ستون image تأیید نشد. لطفاً لاگ Laravel بررسی شود.');
        }

        return back()->with('success', 'Migration تصویر اعضای هیئت‌مدیره با موفقیت اجرا شد و ستون image ایجاد شد.');
    }

    public function unionManagerProfile(Request $request): View
    {
        $this->ensureSuperAdmin($request);

        $tableExists = Schema::hasTable('unions');

        return view('admin.maintenance.union-manager-profile', [
            'tableExists' => $tableExists,
            'managerPositionExists' => $tableExists && Schema::hasColumn('unions', 'manager_position'),
            'managerDescriptionExists' => $tableExists && Schema::hasColumn('unions', 'manager_description'),
        ]);
    }

    public function runUnionManagerProfile(Request $request): RedirectResponse
    {
        $this->ensureSuperAdmin($request);

        if (! Schema::hasTable('unions')) {
            return back()->with('error', 'جدول unions در دیتابیس پیدا نشد؛ عملیات انجام نشد.');
        }

        $managerPositionExists = Schema::hasColumn('unions', 'manager_position');
        $managerDescriptionExists = Schema::hasColumn('unions', 'manager_description');

        if ($managerPositionExists && $managerDescriptionExists) {
            return back()->with('success', 'هر دو ستون اطلاعات رئیس اتحادیه از قبل وجود دارند و نیازی به اجرای migration نیست.');
        }

        try {
            $exitCode = Artisan::call('migrate', [
                '--path' => self::UNION_MANAGER_PROFILE_MIGRATION,
                '--force' => true,
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', 'اجرای migration اطلاعات رئیس اتحادیه با خطا مواجه شد. جزئیات در لاگ Laravel ثبت شد.');
        }

        $managerPositionExists = Schema::hasColumn('unions', 'manager_position');
        $managerDescriptionExists = Schema::hasColumn('unions', 'manager_description');

        if ($exitCode !== 0 || ! $managerPositionExists || ! $managerDescriptionExists) {
            return back()->with('error', 'migration اجرا شد اما هر دو ستون manager_position و manager_description تأیید نشدند. لطفاً لاگ Laravel بررسی شود.');
        }

        return back()->with('success', 'Migration اطلاعات رئیس اتحادیه با موفقیت اجرا شد و هر دو ستون لازم ایجاد شدند.');
    }

    private function ensureSuperAdmin(Request $request): void
    {
        abort_unless($request->user()?->hasRole('super-admin'), 403);
    }
}
