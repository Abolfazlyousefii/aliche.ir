<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Category;
use App\Models\ChamberMember;
use App\Models\Commission;
use App\Models\CongratulationMessage;
use App\Models\ElectronicService;
use App\Models\Gallery;
use App\Models\GuildUnion;
use App\Models\HomeSection;
use App\Models\OrgLink;
use App\Models\Post;
use App\Models\Price;
use App\Models\System;
use App\Models\TourismPlace;
use App\Models\UnionType;
use App\Models\Video;
use App\Services\AdvertisementService;
use App\Services\DailyNewsService;
use App\Services\MenuService;
use App\Services\SettingService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(AdvertisementService $advertisementService, MenuService $menus, SettingService $settings, DailyNewsService $dailyNewsService): View
    {
        $sections = HomeSection::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $latestPosts = Post::query()
            ->published()
            ->with(['category', 'union', 'featuredMedia'])
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(6, ['*'], 'news_page')
            ->withQueryString();

        $importantPosts = Post::query()
            ->published()
            ->where('homepage_position', 'featured')
            ->with(['category', 'union', 'galleries'])
            ->withCount('galleries')
            ->orderBy('sort_order')
            ->latest('published_at')
            ->take($this->sectionLimit($sections, 'important_news', 6))
            ->get();

        $heroPosts = Post::query()
            ->published()
            ->where('homepage_position', 'top')
            ->with(['category', 'union', 'galleries'])
            ->withCount('galleries')
            ->orderBy('featured_order')
            ->latest('published_at')
            ->latest('id')
            ->take(3)
            ->get();

        if ($heroPosts->isEmpty()) {
            $heroPosts = $latestPosts->getCollection()->take(3);
        }

        $dailySelectedDate = $dailyNewsService->selectedDate(request()->query('date'));
        if ($dailySelectedDate->isFuture()) {
            $dailySelectedDate = now()->startOfDay();
        }
        $dailyLimit = $this->sectionLimit($sections, 'daily_news', 10);
        $dailyPosts = $dailyNewsService->home($dailySelectedDate, $dailyLimit);
        $dailyNewsCount = $dailyNewsService->count($dailySelectedDate);
        $dailyDateLabel = $dailyNewsService->label($dailySelectedDate);
        $dailyDateParam = $dailyNewsService->jalaliParam($dailySelectedDate);
        $dailyPreviousDateParam = $dailyNewsService->jalaliParam($dailySelectedDate->copy()->subDay());
        $dailyNextDateParam = $dailyNewsService->jalaliParam($dailySelectedDate->copy()->addDay());
        $dailyIsToday = $dailySelectedDate->isSameDay(now());

        $sidePosts = Post::query()
            ->published()
            ->where('homepage_position', 'featured')
            ->with(['category', 'union', 'galleries'])
            ->withCount('galleries')
            ->latest('published_at')
            ->latest('id')
            ->take(2)
            ->get();

        if ($sidePosts->isEmpty()) {
            $sidePosts = $latestPosts->getCollection()->whereNotIn('id', $heroPosts->pluck('id'))->take(2)->values();
        }

        $announcements = Announcement::query()
            ->published()
            ->shownOnHome()
            ->with(['category', 'union'])
            ->orderBy('sort_order')
            ->latest('published_at')
            ->take($this->sectionLimit($sections, 'announcements', 5))
            ->get();

        if ($announcements->isEmpty()) {
            $announcements = Announcement::query()
                ->published()
                ->with(['category', 'union'])
                ->orderBy('sort_order')
                ->latest('published_at')
                ->take($this->sectionLimit($sections, 'announcements', 5))
                ->get();
        }

        $importantAnnouncements = $announcements->where('is_important', true)->values();
        if ($importantAnnouncements->isEmpty()) {
            $importantAnnouncements = $announcements;
        }

        $unionLimit = $this->sectionLimit($sections, 'unions', 24);
        $homeUnions = GuildUnion::query()
            ->active()
            ->withCount(['posts as published_posts_count' => fn ($query) => $query->published()])
            ->orderBy('sort_order')
            ->orderBy('title')
            ->take($unionLimit)
            ->get();

        $unionTypes = UnionType::query()->active()->orderBy('sort_order')->orderBy('title')->get();
        $unionTypeTabs = $unionTypes->mapWithKeys(fn (UnionType $unionType) => [
            $unionType->slug => [
                'label' => $unionType->title,
                'icon' => $unionType->icon,
                'items' => $this->unionsByTypeDefinition($unionType, $unionLimit),
            ],
        ]);

        $productionUnions = $unionTypeTabs->get('production')['items'] ?? $this->unionsByType(GuildUnion::TYPE_PRODUCTION);
        $distributionUnions = $unionTypeTabs->get('distribution')['items'] ?? $this->unionsByType(GuildUnion::TYPE_DISTRIBUTION);
        $serviceUnions = $unionTypeTabs->get('service')['items'] ?? $this->unionsByType(GuildUnion::TYPE_SERVICE);

        $unionPanels = $unionTypeTabs
            ->mapWithKeys(fn (array $data, string $slug) => ['rep-'.$slug => $data])
            ->filter(fn (array $data) => collect($data['items'] ?? [])->isNotEmpty());

        if ($unionPanels->isEmpty()) {
            $legacyPanels = collect([
                'rep-production' => ['label' => 'اتحادیه‌های تولیدی', 'icon' => '', 'items' => $productionUnions],
                'rep-distribution' => ['label' => 'اتحادیه‌های توزیعی', 'icon' => '', 'items' => $distributionUnions],
                'rep-service' => ['label' => 'اتحادیه‌های خدماتی', 'icon' => '', 'items' => $serviceUnions],
            ]);
            $unionPanels = $legacyPanels->filter(fn (array $data) => collect($data['items'] ?? [])->isNotEmpty());
        }

        if ($unionPanels->isEmpty() && $homeUnions->isNotEmpty()) {
            $unionPanels = collect([
                'rep-all' => ['label' => 'همه اتحادیه‌های فعال', 'icon' => '', 'items' => $homeUnions],
            ]);
        }

        $electronicServices = ElectronicService::query()
            ->published()
            ->with('category')
            ->orderBy('sort_order')
            ->latest('published_at')
            ->take($this->sectionLimit($sections, 'electronic_services', 6))
            ->get();

        $systems = System::query()
            ->published()
            ->with('category')
            ->orderBy('sort_order')
            ->latest('published_at')
            ->take($this->sectionLimit($sections, 'systems', 6))
            ->get();

        $galleries = Gallery::query()
            ->published()
            ->forHome()
            ->with('union')
            ->withCount('images')
            ->orderBy('sort_order')
            ->latest('published_at')
            ->take($this->sectionLimit($sections, 'galleries', 8))
            ->get();
        $latestGalleries = $galleries;

        $latestVideos = Video::query()
            ->published()
            ->with('union')
            ->orderBy('sort_order')
            ->latest('published_at')
            ->take($this->sectionLimit($sections, 'videos', 5))
            ->get();

        $tourismLimit = $this->sectionLimit($sections, 'tourism', 4);
        $tourismCategories = $this->tourismCategories();
        $tourismPanels = $this->tourismPanelsByCategory($tourismCategories, $tourismLimit);
        $tourismPlaces = $tourismPanels->flatMap(fn (array $panel) => $panel['items'])->values();
        $tourismNature = $tourismPanels->first()['items'] ?? collect();
        $tourismHistoric = $tourismPanels->skip(1)->first()['items'] ?? collect();
        $tourismShop = $tourismPanels->skip(2)->first()['items'] ?? collect();

        $commissions = Commission::query()
            ->published()
            ->with(['activeTasks'])
            ->withCount(['publishedSessions as sessions_count'])
            ->orderBy('sort_order')
            ->latest('published_at')
            ->take($this->sectionLimit($sections, 'commissions', 8))
            ->get();

        $chamberMembers = ChamberMember::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->take(5)
            ->get();

        $congratulationMessages = CongratulationMessage::where('is_active', true)
            ->with('union')
            ->where('status', 'published')
            ->where('show_on_home', true)
            ->latest('published_at')
            ->take($this->sectionLimit($sections, 'congratulation_messages', 6))
            ->get();

        $orgLinks = OrgLink::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $advertisementPosition = (string) (data_get($sections->firstWhere('key', 'advertisements')?->settings, 'position') ?: 'home_top');
        $sidebarAdvertisements = $advertisementService->getByPosition($advertisementPosition, 4);
        $bannerAdvertisements = $advertisementService->getByPosition('home_middle', 4);

        if ($sidebarAdvertisements->isEmpty()) {
            $sidebarAdvertisements = $advertisementService->getDisplayable(4);
        }

        if ($bannerAdvertisements->isEmpty()) {
            $bannerAdvertisements = $advertisementService->getByPosition('home_top', 4);
        }

        if ($bannerAdvertisements->isEmpty()) {
            $bannerAdvertisements = $advertisementService->getDisplayable(4);
        }

        $quickMenuItems = $menus->items('quick');
        $quickMenu = $quickMenuItems;
        $mainMenu = $menus->items('main');
        $footerMenu = $menus->items('footer');
        $priceItems = $this->priceItems($settings);
        $siteSettings = $settings->all();
        $homeSections = $sections;
        $unions = $homeUnions;
        $videos = $latestVideos;
        $homeAdvertisements = $bannerAdvertisements;
        $advertisements = $bannerAdvertisements;

        return view('frontend.home', compact(
            'sections',
            'importantPosts',
            'latestPosts',
            'dailyPosts',
            'dailyNewsCount',
            'dailyDateLabel',
            'dailyDateParam',
            'dailyPreviousDateParam',
            'dailyNextDateParam',
            'dailyIsToday',
            'heroPosts',
            'sidePosts',
            'importantAnnouncements',
            'announcements',
            'homeUnions',
            'unions',
            'unionTypes',
            'unionTypeTabs',
            'productionUnions',
            'distributionUnions',
            'serviceUnions',
            'unionPanels',
            'electronicServices',
            'galleries',
            'latestGalleries',
            'latestVideos',
            'videos',
            'tourismPlaces',
            'tourismCategories',
            'tourismPanels',
            'tourismNature',
            'tourismHistoric',
            'tourismShop',
            'systems',
            'commissions',
            'congratulationMessages',
            'chamberMembers',
            'orgLinks',
            'homeAdvertisements',
            'sidebarAdvertisements',
            'bannerAdvertisements',
            'advertisements',
            'quickMenuItems',
            'quickMenu',
            'mainMenu',
            'footerMenu',
            'siteSettings',
            'homeSections',
            'priceItems'
        ));
    }

    private function unionsByType(string $type): Collection
    {
        return GuildUnion::query()
            ->active()
            ->where('union_type', $type)
            ->orderBy('title')
            ->take(10)
            ->get();
    }

    private function unionsByTypeDefinition(UnionType $type, int $limit): Collection
    {
        return GuildUnion::query()
            ->active()
            ->where(fn ($query) => $query
                ->where('union_type_id', $type->id)
                ->orWhere('union_type', $type->slug))
            ->orderBy('sort_order')
            ->orderBy('title')
            ->take($limit)
            ->get();
    }

    private function tourismCategories(): Collection
    {
        return Category::query()
            ->active()
            ->where('type', 'tourism')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();
    }

    private function tourismPanelsByCategory(Collection $categories, int $limit): Collection
    {
        $panels = $categories->mapWithKeys(fn (Category $category) => [
            'tourism-category-'.$category->id => [
                'label' => $category->title,
                'items' => TourismPlace::query()
                    ->published()
                    ->where('category_id', $category->id)
                    ->with('category')
                    ->orderBy('sort_order')
                    ->latest('published_at')
                    ->take($limit)
                    ->get(),
            ],
        ])->filter(fn (array $panel) => $panel['items']->isNotEmpty());

        if ($panels->isNotEmpty()) {
            return $panels;
        }

        return collect([
            'tourism-all' => [
                'label' => 'همه جاذبه‌ها',
                'items' => TourismPlace::query()
                    ->published()
                    ->with('category')
                    ->orderBy('sort_order')
                    ->latest('published_at')
                    ->take($limit)
                    ->get(),
            ],
        ])->filter(fn (array $panel) => $panel['items']->isNotEmpty());
    }

    private function priceItems(SettingService $settings): Collection
    {
        $prices = Price::query()
            ->active()
            ->whereIn('type', ['gold', 'coin', 'silver', 'currency'])
            ->orderBy('sort_order')
            ->latest('published_at')
            ->take(4)
            ->get()
            ->map(fn (Price $price) => [
                'label' => $price->title,
                'value' => filled($price->amount) ? number_format((float) $price->amount) : '—',
                'unit' => $price->unit,
                'trend' => $price->source ?: '',
            ]);

        if ($prices->isNotEmpty()) {
            return $prices->values();
        }

        $defaults = [
            'gold' => ['label' => 'طلا ۱۸ عیار', 'value' => '—', 'unit' => 'تومان', 'trend' => ''],
            'coin' => ['label' => 'سکه امامی', 'value' => '—', 'unit' => 'تومان', 'trend' => ''],
            'silver' => ['label' => 'نقره', 'value' => '—', 'unit' => 'تومان', 'trend' => ''],
            'usd' => ['label' => 'دلار', 'value' => '—', 'unit' => 'تومان', 'trend' => ''],
        ];

        return collect($defaults)->map(function (array $fallback, string $key) use ($settings) {
            $item = (array) $settings->get("prices.{$key}", []);

            return array_merge($fallback, array_filter($item, fn ($value) => filled($value)));
        })->values();
    }

    private function sectionLimit(Collection $sections, string $key, int $default): int
    {
        $settings = $sections->firstWhere('key', $key)?->settings ?? [];

        return max(1, (int) ($settings['limit'] ?? $default));
    }
}
