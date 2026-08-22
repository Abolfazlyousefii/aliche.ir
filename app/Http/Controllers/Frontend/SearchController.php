<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Commission;
use App\Models\ElectronicService;
use App\Models\Gallery;
use App\Models\GuildUnion;
use App\Models\Page;
use App\Models\Post;
use App\Models\System as SystemModel;
use App\Models\TourismPlace;
use App\Models\Video;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $query = trim((string) ($validated['q'] ?? ''));
        $results = [];

        if (mb_strlen($query) >= 2) {
            $term = '%'.addcslashes($query, '%_\\').'%';
            $results = [
                'news' => ['title' => 'اخبار', 'items' => $this->posts($term)],
                'announcements' => ['title' => 'اطلاعیه‌ها', 'items' => $this->announcements($term)],
                'pages' => ['title' => 'صفحات', 'items' => $this->pages($term)],
                'unions' => ['title' => 'اتحادیه‌ها', 'items' => $this->unions($term)],
                'services' => ['title' => 'خدمات الکترونیک', 'items' => $this->services($term)],
                'tourism' => ['title' => 'گردشگری', 'items' => $this->tourism($term)],
                'commissions' => ['title' => 'کمیسیون‌ها', 'items' => $this->commissions($term)],
                'systems' => ['title' => 'سامانه‌ها', 'items' => $this->systems($term)],
                'videos' => ['title' => 'ویدیوها', 'items' => $this->videos($term)],
                'galleries' => ['title' => 'گالری‌ها', 'items' => $this->galleries($term)],
            ];
        }

        return view('frontend.search.index', compact('query', 'results'));
    }

    private function posts(string $term): array
    {
        return Post::query()->published()->editorial()->where(fn ($q) => $this->like($q, ['title', 'excerpt', 'body'], $term))->latest('published_at')->limit(5)->get()
            ->map(fn ($item) => $this->result($item->title, 'اخبار', $item->excerpt ?: $item->body, route('posts.show', $item->slug), $item->featured_image))->all();
    }

    private function announcements(string $term): array
    {
        return Announcement::query()->published()->where(fn ($q) => $this->like($q, ['title', 'excerpt', 'body'], $term))->latest('published_at')->limit(5)->get()
            ->map(fn ($item) => $this->result($item->title, 'اطلاعیه', $item->excerpt ?: $item->body, route('announcements.show', $item->slug), $item->featured_image))->all();
    }

    private function pages(string $term): array
    {
        return Page::query()->published()->where(fn ($q) => $this->like($q, ['title', 'excerpt', 'body'], $term))->latest('published_at')->limit(5)->get()
            ->map(fn ($item) => $this->result($item->title, 'صفحه', $item->excerpt ?: $item->body, route('pages.show', $item->slug), $item->featured_image))->all();
    }

    private function unions(string $term): array
    {
        return GuildUnion::query()->active()->where(fn ($q) => $this->like($q, ['title', 'name', 'short_description', 'description'], $term))->orderBy('title')->limit(5)->get()
            ->map(fn ($item) => $this->result($item->display_title, 'اتحادیه', $item->short_description ?: $item->description, route('guilds.show', $item->slug), $item->primary_image))->all();
    }

    private function services(string $term): array
    {
        return ElectronicService::query()->published()->where(fn ($q) => $this->like($q, ['title', 'short_description', 'body'], $term))->orderBy('title')->limit(5)->get()
            ->map(fn ($item) => $this->result($item->title, 'خدمات الکترونیک', $item->short_description ?: $item->body, route('electronic-services.show', $item->slug), $item->image))->all();
    }

    private function tourism(string $term): array
    {
        return TourismPlace::query()->published()->where(fn ($q) => $this->like($q, ['title', 'short_description', 'description', 'address'], $term))->orderBy('title')->limit(5)->get()
            ->map(fn ($item) => $this->result($item->title, 'گردشگری', $item->short_description ?: $item->description, route('tourism.show', $item->slug), $item->featured_image))->all();
    }

    private function commissions(string $term): array
    {
        return Commission::query()->published()->where(fn ($q) => $this->like($q, ['title', 'description'], $term))->orderBy('title')->limit(5)->get()
            ->map(fn ($item) => $this->result($item->title, 'کمیسیون', $item->description, route('commissions.show', $item->slug), $item->image))->all();
    }

    private function systems(string $term): array
    {
        return SystemModel::query()->published()->where(fn ($q) => $this->like($q, ['title', 'short_description', 'description'], $term))->orderBy('title')->limit(5)->get()
            ->map(fn ($item) => $this->result($item->title, 'سامانه', $item->short_description ?: $item->description, route('systems.show', $item->slug), $item->image))->all();
    }

    private function videos(string $term): array
    {
        return Video::query()->published()->where(fn ($q) => $this->like($q, ['title', 'description'], $term))->orderBy('title')->limit(5)->get()
            ->map(fn ($item) => $this->result($item->title, 'ویدیو', $item->description, route('videos.show', $item->slug), $item->cover_image))->all();
    }

    private function galleries(string $term): array
    {
        return Gallery::query()->published()->where(fn ($q) => $this->like($q, ['title', 'description'], $term))->orderBy('title')->limit(5)->get()
            ->map(fn ($item) => $this->result($item->title, 'گالری', $item->description, route('galleries.show', $item->slug), $item->cover_image))->all();
    }

    private function like(Builder $query, array $columns, string $term): void
    {
        foreach ($columns as $column) {
            $query->orWhere($column, 'like', $term);
        }
    }

    private function result(string $title, string $type, ?string $summary, string $url, ?string $image = null): array
    {
        return compact('title', 'type', 'url', 'image') + ['summary' => plain_text($summary, 160)];
    }
}
