<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\AdvertisementController as AdminAdvertisementController;
use App\Http\Controllers\Admin\AdvertisementPositionController as AdminAdvertisementPositionController;
use App\Http\Controllers\Admin\AnnouncementController as AdminAnnouncementController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\ChamberMemberController;
use App\Http\Controllers\Admin\ComplaintController as AdminComplaintController;
use App\Http\Controllers\Admin\CongratulationMessageController as AdminCongratulationMessageController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\CommissionController as AdminCommissionController;
use App\Http\Controllers\Admin\CommissionSessionController as AdminCommissionSessionController;
use App\Http\Controllers\Admin\ElectronicServiceController as AdminElectronicServiceController;
use App\Http\Controllers\Admin\FooterSettingController;
use App\Http\Controllers\Admin\GalleryController as AdminGalleryController;
use App\Http\Controllers\Admin\HeaderSettingController;
use App\Http\Controllers\Admin\HomeSectionController;
use App\Http\Controllers\Admin\InternalMessageController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\MenuItemController;
use App\Http\Controllers\Admin\MediaController as AdminMediaController;
use App\Http\Controllers\Admin\PageController as AdminPageController;
use App\Http\Controllers\Admin\PendingApprovalController;
use App\Http\Controllers\Admin\PostController as AdminPostController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\RichTextUploadController;
use App\Http\Controllers\Admin\SiteSettingController;
use App\Http\Controllers\Admin\SmsController;
use App\Http\Controllers\Admin\SystemController as AdminSystemController;
use App\Http\Controllers\Admin\TourismPlaceController as AdminTourismPlaceController;
use App\Http\Controllers\Admin\UnionController as AdminUnionController;
use App\Http\Controllers\Admin\UnionTypeController as AdminUnionTypeController;
use App\Http\Controllers\Admin\UnionMemberController;
use App\Http\Controllers\Admin\VideoController as AdminVideoController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Frontend\AnnouncementController as FrontendAnnouncementController;
use App\Http\Controllers\Frontend\ChamberMemberController as FrontendChamberMemberController;
use App\Http\Controllers\Frontend\ComplaintController as FrontendComplaintController;
use App\Http\Controllers\Frontend\CommissionController as FrontendCommissionController;
use App\Http\Controllers\Frontend\CongratulationMessageController as FrontendCongratulationMessageController;
use App\Http\Controllers\Frontend\ContactController as FrontendContactController;
use App\Http\Controllers\Frontend\DailyNewsController as FrontendDailyNewsController;
use App\Http\Controllers\Frontend\ElectronicServiceController as FrontendElectronicServiceController;
use App\Http\Controllers\Frontend\GalleryController as FrontendGalleryController;
use App\Http\Controllers\Frontend\HomeController as FrontendHomeController;
use App\Http\Controllers\Frontend\PageController as FrontendPageController;
use App\Http\Controllers\Frontend\PostController as FrontendPostController;
use App\Http\Controllers\Frontend\SearchController as FrontendSearchController;
use App\Http\Controllers\Frontend\SystemController as FrontendSystemController;
use App\Http\Controllers\Frontend\TourismController as FrontendTourismController;
use App\Http\Controllers\Frontend\UnionController as FrontendUnionController;
use App\Http\Controllers\Frontend\UnionPresidentController;
use App\Http\Controllers\Frontend\VideoController as FrontendVideoController;
use App\Http\Controllers\Admin\PermissionController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;


Route::middleware('guest')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->middleware('throttle:5,1');
});
Route::post('/logout', [AdminAuthController::class, 'logout'])->middleware('auth')->name('logout');

$servePublicMedia = function (string $path) {
    $path = App\Support\PublicFileUrl::normalizeStoragePath($path);

    abort_if($path === '' || str_contains($path, '..'), 404);

    $roots = array_unique(array_filter([
        config('filesystems.disks.public.root'),
        storage_path('app/public'),
    ]));

    foreach ($roots as $root) {
        $file = rtrim($root, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$path;

        if (is_file($file)) {
            return response()->file($file);
        }
    }

    $fallback = public_path('assets/img/asnaf-gorgan-default.jpg');
    $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

    if (in_array($extension, ['avif', 'gif', 'jpeg', 'jpg', 'png', 'webp'], true) && is_file($fallback)) {
        return response()->file($fallback);
    }

    abort(404);
};

Route::get('/media/{path}', function (string $path) {
    $path = App\Support\PublicFileUrl::normalizeStoragePath($path);

    abort_if($path === '', 404);

    return redirect()->to(Storage::disk('public')->url($path));
})->where('path', '.*')->name('media.public');
Route::get('/uploaded-media/{path}', $servePublicMedia)->where('path', '.*')->name('media.public.uploaded-media');
Route::get('/media-files/{path}', $servePublicMedia)->where('path', '.*')->name('media.public.legacy');

Route::get('/storage/assets/{path}', function (string $path) {
    return redirect()->to(asset('assets/'.$path));
})->where('path', '.*')->name('storage.assets.fallback');

Route::get('/storage/{path}', function (string $path) {
    return redirect()->route('media.public', ['path' => $path]);
})->where('path', '.*')->name('storage.public.fallback');

Route::get('/', [FrontendHomeController::class, 'index'])->name('home');
Route::get('/home/latest-news', [FrontendHomeController::class, 'latestNews'])
    ->middleware('throttle:60,1')
    ->name('home.latest-news');
Route::get('/guilds', [FrontendUnionController::class, 'index'])->name('guilds.index');
Route::get('/chamber-members', [FrontendChamberMemberController::class, 'index'])->name('chamber-members.index');
Route::get('/union-presidents', [UnionPresidentController::class, 'index'])->name('union-presidents.index');
Route::get('/guilds/ajax-search', [FrontendUnionController::class, 'ajaxSearch'])->middleware('throttle:60,1')->name('guilds.ajax-search');
Route::get('/guilds/search', [FrontendUnionController::class, 'ajaxSearch'])->middleware('throttle:60,1')->name('guilds.search');
Route::get('/guilds/{union:slug}', [FrontendUnionController::class, 'show'])->name('guilds.show');
Route::get('/daily-news', [FrontendDailyNewsController::class, 'index'])->name('daily-news.index');
Route::get('/posts', [FrontendPostController::class, 'index'])->name('posts.index');
Route::get('/news/{slug}', [FrontendPostController::class, 'show'])->name('posts.show');
Route::get('/posts/{slug}', [FrontendPostController::class, 'legacy'])->name('posts.legacy');
Route::get('/announcements', [FrontendAnnouncementController::class, 'index'])->name('announcements.index');
Route::get('/announcements/{announcement:slug}', [FrontendAnnouncementController::class, 'show'])->name('announcements.show');
Route::get('/tourism', [FrontendTourismController::class, 'index'])->name('tourism.index');
Route::get('/tourism/{tourismPlace:slug}', [FrontendTourismController::class, 'show'])->name('tourism.show');
Route::get('/galleries', [FrontendGalleryController::class, 'index'])->name('galleries.index');
Route::get('/galleries/{gallery:slug}', [FrontendGalleryController::class, 'show'])->name('galleries.show');
Route::get('/videos', [FrontendVideoController::class, 'index'])->name('videos.index');
Route::get('/videos/{video:slug}', [FrontendVideoController::class, 'show'])->name('videos.show');
Route::get('/systems', [FrontendSystemController::class, 'index'])->name('systems.index');
Route::get('/systems/{system:slug}', [FrontendSystemController::class, 'show'])->name('systems.show');
Route::get('/electronic-services', [FrontendElectronicServiceController::class, 'index'])->name('electronic-services.index');
Route::get('/electronic-services/{electronicService:slug}', [FrontendElectronicServiceController::class, 'show'])->name('electronic-services.show');
Route::get('/commissions', [FrontendCommissionController::class, 'index'])->name('commissions.index');
Route::get('/commissions/{commission:slug}', [FrontendCommissionController::class, 'show'])->name('commissions.show');
Route::get('/congratulation-messages/{slug}', [FrontendCongratulationMessageController::class, 'show'])->name('congratulation_messages.show');
Route::get('/search', [FrontendSearchController::class, 'index'])->name('search');
Route::get('/contact', [FrontendContactController::class, 'create'])->name('contact.create');
Route::post('/contact', [FrontendContactController::class, 'store'])->middleware('throttle:5,1')->name('contact.store');
Route::get('/pages/{page:slug}', [FrontendPageController::class, 'show'])->name('pages.show');
Route::get('/complaints/create/{union?}', [FrontendComplaintController::class, 'create'])->name('complaints.create');
Route::post('/complaints', [FrontendComplaintController::class, 'store'])->middleware('throttle:3,1')->name('complaints.store');
Route::get('/complaints/track', [FrontendComplaintController::class, 'track'])->name('complaints.track');
Route::post('/complaints/track', [FrontendComplaintController::class, 'trackResult'])->middleware('throttle:10,1')->name('complaints.track.result');
Route::post('/complaints/lookup', [FrontendComplaintController::class, 'lookup'])->middleware('throttle:10,1')->name('complaints.lookup');

Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::post('rich-text/upload', [RichTextUploadController::class, 'store'])
        ->middleware('throttle:30,1')
        ->name('rich_text.upload');

    Route::get('/', [AdminDashboardController::class, 'index'])
        ->middleware('permission:dashboard.view')
        ->name('dashboard');

    Route::get('pending-approvals', [PendingApprovalController::class, 'index'])
        ->middleware('permission:pending_approvals.view')
        ->name('pending_approvals.index');

    Route::get('messages', [InternalMessageController::class, 'index'])
        ->name('messages.index')
        ->middleware('permission:messages.view');
    Route::get('messages/inbox', [InternalMessageController::class, 'inbox'])
        ->name('messages.inbox');
    Route::get('messages/sent', [InternalMessageController::class, 'sent'])
        ->name('messages.sent');
    Route::get('messages/create', [InternalMessageController::class, 'create'])
        ->name('messages.create')
        ->middleware('permission:messages.send');
    Route::post('messages', [InternalMessageController::class, 'store'])
        ->name('messages.store')
        ->middleware('permission:messages.send');
    Route::get('messages/{message}', [InternalMessageController::class, 'show'])
        ->name('messages.show');
    Route::post('messages/{message}/reply', [InternalMessageController::class, 'storeReply'])
        ->name('messages.reply')
        ->middleware('permission:messages.reply');
    Route::delete('messages/{message}', [InternalMessageController::class, 'destroy'])
        ->name('messages.destroy')
        ->middleware('permission:messages.delete');
    Route::patch('pending-approvals/{type}/{id}/approve', [PendingApprovalController::class, 'approve'])
        ->name('pending_approvals.approve');
    Route::patch('pending-approvals/{type}/{id}/reject', [PendingApprovalController::class, 'reject'])
        ->name('pending_approvals.reject');
    Route::patch('pending-approvals/{type}/{id}/publish', [PendingApprovalController::class, 'publish'])
        ->name('pending_approvals.publish');
    Route::patch('pending-approvals/{type}/{id}/archive', [PendingApprovalController::class, 'archive'])
        ->name('pending_approvals.archive');

    Route::resource('categories', AdminCategoryController::class)->except(['show']);
    Route::resource('chamber-members', ChamberMemberController::class)->except(['show'])->names('chamber_members');
    Route::resource('union-types', AdminUnionTypeController::class)->except(['show']);

    Route::get('pages', [AdminPageController::class, 'index'])->middleware('permission:pages.view')->name('pages.index');
    Route::get('pages/create', [AdminPageController::class, 'create'])->middleware('permission:pages.create')->name('pages.create');
    Route::post('pages', [AdminPageController::class, 'store'])->middleware('permission:pages.create')->name('pages.store');
    Route::get('pages/{page}', [AdminPageController::class, 'show'])->middleware('permission:pages.view')->name('pages.show');
    Route::get('pages/{page}/edit', [AdminPageController::class, 'edit'])->middleware('permission:pages.edit')->name('pages.edit');
    Route::put('pages/{page}', [AdminPageController::class, 'update'])->middleware('permission:pages.edit')->name('pages.update');
    Route::delete('pages/{page}', [AdminPageController::class, 'destroy'])->middleware('permission:pages.delete')->name('pages.destroy');
    Route::patch('pages/{page}/approve', [AdminPageController::class, 'approve'])->middleware('permission:pages.approve')->name('pages.approve');
    Route::patch('pages/{page}/publish', [AdminPageController::class, 'publish'])->middleware('permission:pages.publish')->name('pages.publish');
    Route::patch('pages/{page}/reject', [AdminPageController::class, 'reject'])->middleware('permission:pages.approve')->name('pages.reject');

    Route::get('media', [AdminMediaController::class, 'index'])->middleware('permission:media.view')->name('media.index');
    Route::get('media-picker', [AdminMediaController::class, 'picker'])->middleware('permission:media.view')->name('media.picker');
    Route::post('media', [AdminMediaController::class, 'store'])->middleware('permission:media.upload')->name('media.store');
    Route::put('media/{medium}', [AdminMediaController::class, 'update'])->middleware('permission:media.edit')->name('media.update');
    Route::patch('media/{medium}', [AdminMediaController::class, 'update'])->middleware('permission:media.edit');
    Route::delete('media/{medium}', [AdminMediaController::class, 'destroy'])->middleware('permission:media.delete')->name('media.destroy');
    Route::get('posts', [AdminPostController::class, 'index'])->middleware('permission:posts.view')->name('posts.index');
    Route::get('posts/create', [AdminPostController::class, 'create'])->middleware('permission:posts.create')->name('posts.create');
    Route::post('posts', [AdminPostController::class, 'store'])->middleware('permission:posts.create')->name('posts.store');
    Route::get('posts/{post}', [AdminPostController::class, 'show'])->middleware('permission:posts.view')->name('posts.show');
    Route::get('posts/{post}/edit', [AdminPostController::class, 'edit'])->middleware('permission:posts.edit')->name('posts.edit');
    Route::put('posts/{post}', [AdminPostController::class, 'update'])->middleware('permission:posts.edit')->name('posts.update');
    Route::delete('posts/{post}', [AdminPostController::class, 'destroy'])->middleware('permission:posts.delete')->name('posts.destroy');
    Route::patch('posts/{post}/approve', [AdminPostController::class, 'approve'])->middleware('permission:posts.approve')->name('posts.approve');
    Route::patch('posts/{post}/publish', [AdminPostController::class, 'publish'])->middleware('permission:posts.publish')->name('posts.publish');
    Route::patch('posts/{post}/unpublish', [AdminPostController::class, 'unpublish'])->middleware('permission:posts.publish')->name('posts.unpublish');
    Route::patch('posts/{post}/reject', [AdminPostController::class, 'reject'])->middleware('permission:posts.approve')->name('posts.reject');

    Route::get('announcements', [AdminAnnouncementController::class, 'index'])->middleware('permission:announcements.view')->name('announcements.index');
    Route::get('announcements/create', [AdminAnnouncementController::class, 'create'])->middleware('permission:announcements.create')->name('announcements.create');
    Route::post('announcements', [AdminAnnouncementController::class, 'store'])->middleware('permission:announcements.create')->name('announcements.store');
    Route::get('announcements/{announcement}', [AdminAnnouncementController::class, 'show'])->middleware('permission:announcements.view')->name('announcements.show');
    Route::get('announcements/{announcement}/edit', [AdminAnnouncementController::class, 'edit'])->middleware('permission:announcements.edit')->name('announcements.edit');
    Route::put('announcements/{announcement}', [AdminAnnouncementController::class, 'update'])->middleware('permission:announcements.edit')->name('announcements.update');
    Route::delete('announcements/{announcement}', [AdminAnnouncementController::class, 'destroy'])->middleware('permission:announcements.delete')->name('announcements.destroy');
    Route::patch('announcements/{announcement}/approve', [AdminAnnouncementController::class, 'approve'])->middleware('permission:announcements.approve')->name('announcements.approve');
    Route::patch('announcements/{announcement}/publish', [AdminAnnouncementController::class, 'publish'])->middleware('permission:announcements.publish')->name('announcements.publish');
    Route::patch('announcements/{announcement}/reject', [AdminAnnouncementController::class, 'reject'])->middleware('permission:announcements.approve')->name('announcements.reject');

    Route::get('unions', [AdminUnionController::class, 'index'])->middleware('permission:unions.view')->name('unions.index');
    Route::get('unions/create', [AdminUnionController::class, 'create'])->middleware('permission:unions.create')->name('unions.create');
    Route::post('unions', [AdminUnionController::class, 'store'])->middleware('permission:unions.create')->name('unions.store');
    Route::get('unions/{union}', [AdminUnionController::class, 'show'])->middleware('permission:unions.view')->name('unions.show');
    Route::get('unions/{union}/edit', [AdminUnionController::class, 'edit'])->middleware('permission:unions.edit')->name('unions.edit');
    Route::put('unions/{union}', [AdminUnionController::class, 'update'])->middleware('permission:unions.edit')->name('unions.update');
    Route::delete('unions/{union}', [AdminUnionController::class, 'destroy'])->middleware('permission:unions.delete')->name('unions.destroy');

    Route::get('complaints', [AdminComplaintController::class, 'index'])->middleware('permission:complaints.view')->name('complaints.index');
    Route::get('complaints/{complaint}', [AdminComplaintController::class, 'show'])->middleware('permission:complaints.view')->name('complaints.show');
    Route::get('complaints/{complaint}/edit', [AdminComplaintController::class, 'edit'])->middleware('permission:complaints.edit')->name('complaints.edit');
    Route::put('complaints/{complaint}', [AdminComplaintController::class, 'update'])->middleware('permission:complaints.edit')->name('complaints.update');
    Route::patch('complaints/{complaint}/reply', [AdminComplaintController::class, 'reply'])->middleware('permission:complaints.reply')->name('complaints.reply');
    Route::get('complaints/{complaint}/download', [AdminComplaintController::class, 'download'])->middleware('permission:complaints.view')->name('complaints.download');
    Route::delete('complaints/{complaint}', [AdminComplaintController::class, 'destroy'])->middleware('permission:complaints.delete')->name('complaints.destroy');

    Route::get('contact-messages', [ContactMessageController::class, 'index'])->middleware('permission:contact_messages.view')->name('contact_messages.index');
    Route::get('contact-messages/{contactMessage}', [ContactMessageController::class, 'show'])->middleware('permission:contact_messages.view')->name('contact_messages.show');
    Route::patch('contact-messages/{contactMessage}/mark-read', [ContactMessageController::class, 'markAsRead'])->middleware('permission:contact_messages.view')->name('contact_messages.mark_read');
    Route::delete('contact-messages/{contactMessage}', [ContactMessageController::class, 'destroy'])->middleware('permission:contact_messages.delete')->name('contact_messages.destroy');

    Route::get('sms', [SmsController::class, 'index'])->middleware('permission:sms.view')->name('sms.index');
    Route::get('sms/create', [SmsController::class, 'create'])->middleware('permission:sms.send')->name('sms.create');
    Route::post('sms', [SmsController::class, 'store'])->middleware('permission:sms.send')->name('sms.store');
    Route::get('sms/logs', [SmsController::class, 'logs'])->middleware('permission:sms.logs')->name('sms.logs');
    Route::get('sms/logs/{smsLog}', [SmsController::class, 'show'])->middleware('permission:sms.logs')->name('sms.show');

    Route::get('advertisement-positions', [AdminAdvertisementPositionController::class, 'index'])->middleware('permission:advertisements.view')->name('advertisement_positions.index');
    Route::get('advertisement-positions/create', [AdminAdvertisementPositionController::class, 'create'])->middleware('permission:advertisements.create')->name('advertisement_positions.create');
    Route::post('advertisement-positions', [AdminAdvertisementPositionController::class, 'store'])->middleware('permission:advertisements.create')->name('advertisement_positions.store');
    Route::get('advertisement-positions/{advertisement_position}/edit', [AdminAdvertisementPositionController::class, 'edit'])->middleware('permission:advertisements.edit')->name('advertisement_positions.edit');
    Route::put('advertisement-positions/{advertisement_position}', [AdminAdvertisementPositionController::class, 'update'])->middleware('permission:advertisements.edit')->name('advertisement_positions.update');
    Route::delete('advertisement-positions/{advertisement_position}', [AdminAdvertisementPositionController::class, 'destroy'])->middleware('permission:advertisements.delete')->name('advertisement_positions.destroy');

    Route::get('advertisements', [AdminAdvertisementController::class, 'index'])->middleware('permission:advertisements.view')->name('advertisements.index');
    Route::get('advertisements/create', [AdminAdvertisementController::class, 'create'])->middleware('permission:advertisements.create')->name('advertisements.create');
    Route::post('advertisements', [AdminAdvertisementController::class, 'store'])->middleware('permission:advertisements.create')->name('advertisements.store');
    Route::get('advertisements/{advertisement}', [AdminAdvertisementController::class, 'show'])->middleware('permission:advertisements.view')->name('advertisements.show');
    Route::get('advertisements/{advertisement}/edit', [AdminAdvertisementController::class, 'edit'])->middleware('permission:advertisements.edit')->name('advertisements.edit');
    Route::put('advertisements/{advertisement}', [AdminAdvertisementController::class, 'update'])->middleware('permission:advertisements.edit')->name('advertisements.update');
    Route::delete('advertisements/{advertisement}', [AdminAdvertisementController::class, 'destroy'])->middleware('permission:advertisements.delete')->name('advertisements.destroy');

    Route::get('systems', [AdminSystemController::class, 'index'])->middleware('permission:systems.view')->name('systems.index');
    Route::get('systems/create', [AdminSystemController::class, 'create'])->middleware('permission:systems.create')->name('systems.create');
    Route::post('systems', [AdminSystemController::class, 'store'])->middleware('permission:systems.create')->name('systems.store');
    Route::get('systems/{system}', [AdminSystemController::class, 'show'])->middleware('permission:systems.view')->name('systems.show');
    Route::get('systems/{system}/edit', [AdminSystemController::class, 'edit'])->middleware('permission:systems.edit')->name('systems.edit');
    Route::put('systems/{system}', [AdminSystemController::class, 'update'])->middleware('permission:systems.edit')->name('systems.update');
    Route::delete('systems/{system}', [AdminSystemController::class, 'destroy'])->middleware('permission:systems.delete')->name('systems.destroy');

    Route::get('electronic-services', [AdminElectronicServiceController::class, 'index'])->middleware('permission:electronic_services.view')->name('electronic_services.index');
    Route::get('electronic-services/create', [AdminElectronicServiceController::class, 'create'])->middleware('permission:electronic_services.create')->name('electronic_services.create');
    Route::post('electronic-services', [AdminElectronicServiceController::class, 'store'])->middleware('permission:electronic_services.create')->name('electronic_services.store');
    Route::get('electronic-services/{electronicService}', [AdminElectronicServiceController::class, 'show'])->middleware('permission:electronic_services.view')->name('electronic_services.show');
    Route::get('electronic-services/{electronicService}/edit', [AdminElectronicServiceController::class, 'edit'])->middleware('permission:electronic_services.edit')->name('electronic_services.edit');
    Route::put('electronic-services/{electronicService}', [AdminElectronicServiceController::class, 'update'])->middleware('permission:electronic_services.edit')->name('electronic_services.update');
    Route::delete('electronic-services/{electronicService}', [AdminElectronicServiceController::class, 'destroy'])->middleware('permission:electronic_services.delete')->name('electronic_services.destroy');

    Route::get('congratulation-messages', [AdminCongratulationMessageController::class, 'index'])->middleware('permission:congratulation_messages.view')->name('congratulation_messages.index');
    Route::get('congratulation-messages/create', [AdminCongratulationMessageController::class, 'create'])->middleware('permission:congratulation_messages.create')->name('congratulation_messages.create');
    Route::post('congratulation-messages', [AdminCongratulationMessageController::class, 'store'])->middleware('permission:congratulation_messages.create')->name('congratulation_messages.store');
    Route::get('congratulation-messages/{congratulationMessage}', [AdminCongratulationMessageController::class, 'show'])->middleware('permission:congratulation_messages.view')->name('congratulation_messages.show');
    Route::get('congratulation-messages/{congratulationMessage}/edit', [AdminCongratulationMessageController::class, 'edit'])->middleware('permission:congratulation_messages.edit')->name('congratulation_messages.edit');
    Route::put('congratulation-messages/{congratulationMessage}', [AdminCongratulationMessageController::class, 'update'])->middleware('permission:congratulation_messages.edit')->name('congratulation_messages.update');
    Route::delete('congratulation-messages/{congratulationMessage}', [AdminCongratulationMessageController::class, 'destroy'])->middleware('permission:congratulation_messages.delete')->name('congratulation_messages.destroy');

    Route::get('commissions', [AdminCommissionController::class, 'index'])->middleware('permission:commissions.view')->name('commissions.index');
    Route::get('commissions/create', [AdminCommissionController::class, 'create'])->middleware('permission:commissions.create')->name('commissions.create');
    Route::post('commissions', [AdminCommissionController::class, 'store'])->middleware('permission:commissions.create')->name('commissions.store');
    Route::get('commissions/{commission}', [AdminCommissionController::class, 'show'])->middleware('permission:commissions.view')->name('commissions.show');
    Route::get('commissions/{commission}/edit', [AdminCommissionController::class, 'edit'])->middleware('permission:commissions.edit')->name('commissions.edit');
    Route::put('commissions/{commission}', [AdminCommissionController::class, 'update'])->middleware('permission:commissions.edit')->name('commissions.update');
    Route::delete('commissions/{commission}', [AdminCommissionController::class, 'destroy'])->middleware('permission:commissions.delete')->name('commissions.destroy');

    Route::get('commissions/{commission}/sessions', [AdminCommissionSessionController::class, 'index'])->middleware('permission:commissions.view')->name('commissions.sessions.index');
    Route::get('commissions/{commission}/sessions/create', [AdminCommissionSessionController::class, 'create'])->middleware('permission:commissions.create')->name('commissions.sessions.create');
    Route::post('commissions/{commission}/sessions', [AdminCommissionSessionController::class, 'store'])->middleware('permission:commissions.create')->name('commissions.sessions.store');
    Route::get('commissions/{commission}/sessions/{session}', [AdminCommissionSessionController::class, 'show'])->middleware('permission:commissions.view')->name('commissions.sessions.show');
    Route::get('commissions/{commission}/sessions/{session}/edit', [AdminCommissionSessionController::class, 'edit'])->middleware('permission:commissions.edit')->name('commissions.sessions.edit');
    Route::put('commissions/{commission}/sessions/{session}', [AdminCommissionSessionController::class, 'update'])->middleware('permission:commissions.edit')->name('commissions.sessions.update');
    Route::delete('commissions/{commission}/sessions/{session}', [AdminCommissionSessionController::class, 'destroy'])->middleware('permission:commissions.delete')->name('commissions.sessions.destroy');

    Route::get('settings', [SiteSettingController::class, 'edit'])->middleware('permission:settings.view')->name('settings.edit');
    Route::put('settings', [SiteSettingController::class, 'update'])->middleware('permission:settings.edit')->name('settings.update');
    Route::get('header-settings', [HeaderSettingController::class, 'edit'])->middleware('permission:header_settings.view')->name('header_settings.edit');
    Route::put('header-settings', [HeaderSettingController::class, 'update'])->middleware('permission:header_settings.edit')->name('header_settings.update');
    Route::get('footer-settings', [FooterSettingController::class, 'edit'])->middleware('permission:footer_settings.view')->name('footer_settings.edit');
    Route::put('footer-settings', [FooterSettingController::class, 'update'])->middleware('permission:footer_settings.edit')->name('footer_settings.update');

    Route::get('home-sections', [HomeSectionController::class, 'index'])->middleware('permission:home_sections.view')->name('home_sections.index');
    Route::get('home-sections/{homeSection}/edit', [HomeSectionController::class, 'edit'])->middleware('permission:home_sections.edit')->name('home_sections.edit');
    Route::put('home-sections/{homeSection}', [HomeSectionController::class, 'update'])->middleware('permission:home_sections.edit')->name('home_sections.update');
    Route::post('home-sections/sort', [HomeSectionController::class, 'sort'])->middleware('permission:home_sections.edit')->name('home_sections.sort');

    Route::get('galleries', [AdminGalleryController::class, 'index'])->middleware('permission:galleries.view')->name('galleries.index');
    Route::get('galleries/create', [AdminGalleryController::class, 'create'])->middleware('permission:galleries.create')->name('galleries.create');
    Route::post('galleries', [AdminGalleryController::class, 'store'])->middleware('permission:galleries.create')->name('galleries.store');
    Route::post('galleries/sort', [AdminGalleryController::class, 'sort'])->middleware('permission:galleries.edit')->name('galleries.sort');
    Route::get('galleries/{gallery}', [AdminGalleryController::class, 'show'])->middleware('permission:galleries.view')->name('galleries.show');
    Route::get('galleries/{gallery}/edit', [AdminGalleryController::class, 'edit'])->middleware('permission:galleries.edit')->name('galleries.edit');
    Route::put('galleries/{gallery}', [AdminGalleryController::class, 'update'])->middleware('permission:galleries.edit')->name('galleries.update');
    Route::delete('galleries/{gallery}', [AdminGalleryController::class, 'destroy'])->middleware('permission:galleries.delete')->name('galleries.destroy');

    Route::get('videos', [AdminVideoController::class, 'index'])->middleware('permission:videos.view')->name('videos.index');
    Route::get('videos/create', [AdminVideoController::class, 'create'])->middleware('permission:videos.create')->name('videos.create');
    Route::post('videos', [AdminVideoController::class, 'store'])->middleware('permission:videos.create')->name('videos.store');
    Route::get('videos/{video}', [AdminVideoController::class, 'show'])->middleware('permission:videos.view')->name('videos.show');
    Route::get('videos/{video}/edit', [AdminVideoController::class, 'edit'])->middleware('permission:videos.edit')->name('videos.edit');
    Route::put('videos/{video}', [AdminVideoController::class, 'update'])->middleware('permission:videos.edit')->name('videos.update');
    Route::delete('videos/{video}', [AdminVideoController::class, 'destroy'])->middleware('permission:videos.delete')->name('videos.destroy');

    Route::get('tourism', [AdminTourismPlaceController::class, 'index'])->middleware('permission:tourism.view')->name('tourism.index');
    Route::get('tourism/create', [AdminTourismPlaceController::class, 'create'])->middleware('permission:tourism.create')->name('tourism.create');
    Route::post('tourism', [AdminTourismPlaceController::class, 'store'])->middleware('permission:tourism.create')->name('tourism.store');
    Route::get('tourism/{tourism}', [AdminTourismPlaceController::class, 'show'])->middleware('permission:tourism.view')->name('tourism.show');
    Route::get('tourism/{tourism}/edit', [AdminTourismPlaceController::class, 'edit'])->middleware('permission:tourism.edit')->name('tourism.edit');
    Route::put('tourism/{tourism}', [AdminTourismPlaceController::class, 'update'])->middleware('permission:tourism.edit')->name('tourism.update');
    Route::delete('tourism/{tourism}', [AdminTourismPlaceController::class, 'destroy'])->middleware('permission:tourism.delete')->name('tourism.destroy');

    Route::get('union-members', [UnionMemberController::class, 'index'])->middleware('permission:union_members.view')->name('union_members.index');
    Route::get('union-members/create', [UnionMemberController::class, 'create'])->middleware('permission:union_members.create')->name('union_members.create');
    Route::post('union-members', [UnionMemberController::class, 'store'])->middleware('permission:union_members.create')->name('union_members.store');
    Route::get('union-members/{union_member}', [UnionMemberController::class, 'show'])->middleware('permission:union_members.view')->name('union_members.show');
    Route::get('union-members/{union_member}/edit', [UnionMemberController::class, 'edit'])->middleware('permission:union_members.edit')->name('union_members.edit');
    Route::put('union-members/{union_member}', [UnionMemberController::class, 'update'])->middleware('permission:union_members.edit')->name('union_members.update');
    Route::delete('union-members/{union_member}', [UnionMemberController::class, 'destroy'])->middleware('permission:union_members.delete')->name('union_members.destroy');

    Route::get('menus', [MenuController::class, 'index'])->middleware('permission:menus.view')->name('menus.index');
    Route::get('menus/create', [MenuController::class, 'create'])->middleware('permission:menus.create')->name('menus.create');
    Route::post('menus', [MenuController::class, 'store'])->middleware('permission:menus.create')->name('menus.store');
    Route::get('menus/{menu}', [MenuController::class, 'show'])->middleware('permission:menus.view')->name('menus.show');
    Route::get('menus/{menu}/edit', [MenuController::class, 'edit'])->middleware('permission:menus.edit')->name('menus.edit');
    Route::put('menus/{menu}', [MenuController::class, 'update'])->middleware('permission:menus.edit')->name('menus.update');
    Route::delete('menus/{menu}', [MenuController::class, 'destroy'])->middleware('permission:menus.delete')->name('menus.destroy');

    Route::get('menus/{menu}/items/create', [MenuItemController::class, 'create'])->middleware('permission:menus.create')->name('menus.items.create');
    Route::post('menus/{menu}/items', [MenuItemController::class, 'store'])->middleware('permission:menus.create')->name('menus.items.store');
    Route::get('menus/{menu}/items/{item}/edit', [MenuItemController::class, 'edit'])->middleware('permission:menus.edit')->name('menus.items.edit');
    Route::put('menus/{menu}/items/{item}', [MenuItemController::class, 'update'])->middleware('permission:menus.edit')->name('menus.items.update');
    Route::patch('menus/{menu}/items/{item}/toggle', [MenuItemController::class, 'toggle'])->middleware('permission:menus.edit')->name('menus.items.toggle');
    Route::delete('menus/{menu}/items/{item}', [MenuItemController::class, 'destroy'])->middleware('permission:menus.delete')->name('menus.items.destroy');
    Route::post('menus/{menu}/items/sort', [MenuItemController::class, 'sort'])->middleware('permission:menus.edit')->name('menus.items.sort');

    Route::get('users', [UserController::class, 'index'])->middleware('permission:users.view')->name('users.index');
    Route::get('users/create', [UserController::class, 'create'])->middleware('permission:users.create')->name('users.create');
    Route::post('users', [UserController::class, 'store'])->middleware('permission:users.create')->name('users.store');
    Route::get('users/{user}', [UserController::class, 'show'])->middleware('permission:users.view')->name('users.show');
    Route::get('users/{user}/edit', [UserController::class, 'edit'])->middleware('permission:users.edit')->name('users.edit');
    Route::put('users/{user}', [UserController::class, 'update'])->middleware('permission:users.edit')->name('users.update');
    Route::delete('users/{user}', [UserController::class, 'destroy'])->middleware('permission:users.delete')->name('users.destroy');

    Route::get('roles', [RoleController::class, 'index'])->middleware('permission:roles.view')->name('roles.index');
    Route::get('roles/create', [RoleController::class, 'create'])->middleware('permission:roles.create')->name('roles.create');
    Route::post('roles', [RoleController::class, 'store'])->middleware('permission:roles.create')->name('roles.store');
    Route::get('roles/{role}', [RoleController::class, 'show'])->middleware('permission:roles.view')->name('roles.show');
    Route::get('roles/{role}/edit', [RoleController::class, 'edit'])->middleware('permission:roles.edit')->name('roles.edit');
    Route::put('roles/{role}', [RoleController::class, 'update'])->middleware('permission:roles.edit')->name('roles.update');
    Route::delete('roles/{role}', [RoleController::class, 'destroy'])->middleware('permission:roles.delete')->name('roles.destroy');

    Route::get('permissions', [PermissionController::class, 'index'])->middleware('permission:permissions.view')->name('permissions.index');
    Route::get('permissions/create', [PermissionController::class, 'create'])->middleware('permission:permissions.create')->name('permissions.create');
    Route::post('permissions', [PermissionController::class, 'store'])->middleware('permission:permissions.create')->name('permissions.store');
    Route::get('permissions/{permission}/edit', [PermissionController::class, 'edit'])->middleware('permission:permissions.edit')->name('permissions.edit');
    Route::put('permissions/{permission}', [PermissionController::class, 'update'])->middleware('permission:permissions.edit')->name('permissions.update');
    Route::delete('permissions/{permission}', [PermissionController::class, 'destroy'])->middleware('permission:permissions.delete')->name('permissions.destroy');
});
Route::get('/system/clear-cache-7f3a9d', function (\Illuminate\Http\Request $request) {
    $secretKey = 'Aliche-2026-Clear-Cache-X9p4K7';

    abort_unless(
        hash_equals($secretKey, (string) $request->query('key')),
        403,
        'Access denied.'
    );

    try {
        $results = [];

        \Illuminate\Support\Facades\Artisan::call('optimize:clear');
        $results['optimize:clear'] = trim(
            \Illuminate\Support\Facades\Artisan::output()
        );

        \Illuminate\Support\Facades\Artisan::call('view:clear');
        $results['view:clear'] = trim(
            \Illuminate\Support\Facades\Artisan::output()
        );

        return response()->json([
            'success' => true,
            'message' => 'Laravel caches cleared successfully.',
            'results' => $results,
        ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    } catch (\Throwable $exception) {
        report($exception);

        return response()->json([
            'success' => false,
            'message' => $exception->getMessage(),
        ], 500, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
})->middleware('throttle:2,1');
