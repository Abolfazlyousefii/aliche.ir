<?php

namespace App\Providers;

use App\Models\Announcement;
use App\Models\Commission;
use App\Models\CongratulationMessage;
use App\Models\ElectronicService;
use App\Models\Gallery;
use App\Models\GuildUnion;
use App\Models\InternalMessage;
use App\Models\Page;
use App\Models\Post;
use App\Models\System;
use App\Models\TourismPlace;
use App\Models\Video;
use App\Observers\PostObserver;
use App\Observers\SlugHistoryObserver;
use App\Services\ContentApprovalService;
use App\Support\PublicStorage;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        require_once app_path('Helpers/date.php');
        require_once app_path('Helpers/text.php');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::defaultView('vendor.pagination.bootstrap-rtl');
        PublicStorage::ensureDirectories(['media', 'posts/featured']);

        Post::observe(PostObserver::class);
        app(ContentApprovalService::class)->registerPendingNotificationListeners();

        foreach ([
            Announcement::class,
            Commission::class,
            CongratulationMessage::class,
            ElectronicService::class,
            Gallery::class,
            GuildUnion::class,
            Page::class,
            Post::class,
            System::class,
            TourismPlace::class,
            Video::class,
        ] as $model) {
            $model::observe(SlugHistoryObserver::class);
        }

        View::composer(['admin.partials.sidebar', 'admin.partials.header'], function ($view) {
            $view->with('unreadMessagesCount', auth()->check()
                ? InternalMessage::query()->where('recipient_id', auth()->id())->whereNull('read_at')->count()
                : 0);
        });
    }
}
