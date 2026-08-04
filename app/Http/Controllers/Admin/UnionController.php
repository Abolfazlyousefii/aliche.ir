<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Concerns\SelectsMedia;
use App\Http\Requests\Admin\StoreUnionRequest;
use App\Http\Requests\Admin\UpdateUnionRequest;
use App\Models\Category;
use App\Models\GuildUnion;
use App\Models\Post;
use App\Models\UnionType;
use App\Models\UnionCommission;
use App\Models\UnionEducation;
use App\Models\UnionMinute;
use App\Models\UnionPrice;
use App\Models\UnionRule;
use App\Services\SlugService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class UnionController extends Controller
{
    use SelectsMedia;
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $status = (string) $request->query('status', '');

        $unions = GuildUnion::query()
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('title', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%")
                ->orWhere('manager_name', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('mobile', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")))
            ->when(in_array($status, ['active', 'inactive'], true), fn ($query) => $query->where('is_active', $status === 'active'))
            ->orderBy('sort_order')
            ->orderBy('title')
            ->paginate(15)
            ->withQueryString();

        return view('admin.unions.index', compact('unions', 'search', 'status'));
    }

    public function create(): View
    {
        return view('admin.unions.create', [
            'union' => null,
            'categories' => $this->unionCategories(),
            'unionTypes' => $this->unionTypes(),
            'selectablePosts' => $this->selectablePosts(),
        ]);
    }

    public function store(StoreUnionRequest $request): RedirectResponse
    {
        $data = $this->unionData($request->validated());
        $data['logo'] = $this->storeImage($request, 'logo', 'unions/logos');
        $data['cover_image'] = $this->storeImage($request, 'cover_image', 'unions/covers');
        $data['manager_image'] = $this->storeImage($request, 'manager_image', 'unions/managers');
        $data['price_list_image'] = $this->storeImage($request, 'price_list_image', 'unions/price-lists');

        $union = GuildUnion::create($data);
        $this->syncPageSections($union, $request->validated('related', []));
        $this->syncSelectedPosts($union, $request->validated('selected_posts', []));
        $this->flushFrontendCache();

        return redirect()->route('admin.unions.show', $union)->with('success', 'اتحادیه با موفقیت ایجاد شد.');
    }

    public function show(GuildUnion $union): View
    {
        $union->loadCount(['posts', 'announcements', 'users']);
        $union->load(['commissions.tasks', 'rules', 'minutes', 'educations', 'prices']);

        return view('admin.unions.show', compact('union'));
    }

    public function edit(GuildUnion $union): View
    {
        return view('admin.unions.edit', [
            'union' => $union->load(['commissions.tasks', 'rules', 'minutes', 'educations', 'prices', 'selectedPosts']),
            'categories' => $this->unionCategories(),
            'unionTypes' => $this->unionTypes(),
            'selectablePosts' => $this->selectablePosts(),
        ]);
    }

    public function update(UpdateUnionRequest $request, GuildUnion $union): RedirectResponse
    {
        $data = $this->unionData($request->validated(), $union);

        foreach (['logo' => 'unions/logos', 'cover_image' => 'unions/covers', 'manager_image' => 'unions/managers', 'price_list_image' => 'unions/price-lists'] as $field => $directory) {
            if ($path = $this->storeImage($request, $field, $directory)) {
                if ($union->{$field}) {
                    Storage::disk('public')->delete($union->{$field});
                }

                $data[$field] = $path;
            }
        }

        $union->update($data);
        $this->syncPageSections($union, $request->validated('related', []));
        $this->syncSelectedPosts($union, $request->validated('selected_posts', []));
        $this->flushFrontendCache();

        return redirect()->route('admin.unions.show', $union)->with('success', 'اتحادیه با موفقیت ویرایش شد.');
    }

    public function destroy(GuildUnion $union): RedirectResponse
    {
        foreach (['logo', 'cover_image', 'manager_image'] as $field) {
            if ($union->{$field}) {
                Storage::disk('public')->delete($union->{$field});
            }
        }

        $union->delete();
        $this->flushFrontendCache();

        return redirect()->route('admin.unions.index')->with('success', 'اتحادیه با موفقیت حذف شد.');
    }

    private function flushFrontendCache(): void
    {
        Cache::forget('settings.all');
    }

    /** @param array<string, mixed> $validated @return array<string, mixed> */
    private function unionData(array $validated, ?GuildUnion $union = null): array
    {
        $validated = $this->sanitizeRichTextFields($validated, ['body', 'excerpt', 'description', 'content', 'footer_description', 'site_description']);
        $validated['short_description'] = plain_text($validated['short_description'] ?? null);

        $presidentButtons = collect($validated['president_buttons'] ?? [])->map(function ($button) {
            return [
                'title' => trim((string) ($button['title'] ?? '')),
                'url' => trim((string) ($button['url'] ?? '')),
                'icon' => trim((string) ($button['icon'] ?? '')),
                'target' => in_array(($button['target'] ?? '_self'), ['_self', '_blank'], true) ? $button['target'] : '_self',
                'is_active' => ! empty($button['is_active']),
            ];
        })->filter(fn ($button) => filled($button['title']) && filled($button['url']))->values()->all();

        $socialLinks = collect($validated['social_links'] ?? [])
            ->map(fn ($url) => is_string($url) ? trim($url) : $url)
            ->filter(fn ($url) => filled($url))
            ->all();

        $selectedUnionTypeSlug = null;
        if (! empty($validated['union_type_id'])) {
            $selectedUnionTypeSlug = UnionType::query()->whereKey($validated['union_type_id'])->value('slug');
        }

        $submittedSettings = $validated['settings'] ?? [];
        $settings = collect(GuildUnion::sectionDefaults())
            ->map(fn ($default, $key) => array_key_exists($key, $submittedSettings) ? (bool) $submittedSettings[$key] : false)
            ->all();

        return [
            'name' => $validated['title'],
            'title' => $validated['title'],
            'slug' => app(SlugService::class)->unique(GuildUnion::class, ($validated['slug'] ?? '') ?: $validated['title'], $union?->id, 'slug', 'union'),
            'description' => $validated['description'] ?? null,
            'short_description' => $validated['short_description'] ?? null,
            'address' => $validated['address'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'mobile' => $validated['mobile'] ?? null,
            'email' => $validated['email'] ?? null,
            'website' => $validated['website'] ?? null,
            'manager_name' => $validated['manager_name'] ?? null,
            'union_type' => $selectedUnionTypeSlug ?: ($validated['union_type'] ?? null),
            'union_type_id' => $validated['union_type_id'] ?? null,
            'category_id' => $validated['category_id'] ?? null,
            'working_hours' => $validated['working_hours'] ?? null,
            'social_links' => $socialLinks === [] ? null : $socialLinks,
            'settings' => $settings,
            'news_mode' => $validated['news_mode'] ?? 'auto',
            'president_buttons' => $presidentButtons === [] ? null : $presidentButtons,
            'price_list_mode' => $validated['price_list_mode'],
            'complaint_enabled' => (bool) $validated['complaint_enabled'],
            'congratulations_enabled' => (bool) $validated['congratulations_enabled'],
            'news_enabled' => (bool) $validated['news_enabled'],
            'announcements_enabled' => (bool) $validated['announcements_enabled'],
            'gallery_enabled' => (bool) $validated['gallery_enabled'],
            'videos_enabled' => (bool) $validated['videos_enabled'],
            'members_enabled' => (bool) $validated['members_enabled'],
            'services_enabled' => (bool) $validated['services_enabled'],
            'is_active' => (bool) $validated['is_active'],
            'sort_order' => $validated['sort_order'] ?? 0,
            'meta_title' => $validated['meta_title'] ?? null,
            'meta_description' => $validated['meta_description'] ?? null,
            'meta_keywords' => $validated['meta_keywords'] ?? null,
        ];
    }



    /** @param array<int, mixed> $selectedPosts */
    private function syncSelectedPosts(GuildUnion $union, array $selectedPosts): void
    {
        if (($union->news_mode ?? 'auto') !== 'manual') {
            $union->selectedPosts()->sync([]);
            return;
        }

        $sync = [];
        foreach (array_values($selectedPosts) as $index => $postId) {
            if ($postId) {
                $sync[(int) $postId] = ['sort_order' => ($index + 1) * 10];
            }
        }

        $union->selectedPosts()->sync($sync);
    }

    /** @param array<string, mixed> $related */
    private function syncPageSections(GuildUnion $union, array $related): void
    {
        $this->syncSimpleSection($union, $related['rules'] ?? [], UnionRule::class, ['title', 'description', 'icon', 'file', 'sort_order', 'is_active']);
        $this->syncSimpleSection($union, $related['minutes'] ?? [], UnionMinute::class, ['title', 'meeting_date', 'file', 'description', 'sort_order', 'is_active']);
        $this->syncSimpleSection($union, $related['educations'] ?? [], UnionEducation::class, ['title', 'description', 'icon', 'link', 'sort_order', 'is_active']);
        $this->syncSimpleSection($union, $related['prices'] ?? [], UnionPrice::class, ['title', 'price', 'currency', 'type', 'updated_on', 'sort_order', 'is_active']);
        $this->syncCommissions($union, $related['commissions'] ?? []);
    }

    /** @param array<int, array<string, mixed>> $items @param class-string<\Illuminate\Database\Eloquent\Model> $modelClass @param array<int, string> $fields */
    private function syncSimpleSection(GuildUnion $union, array $items, string $modelClass, array $fields): void
    {
        foreach ($items as $item) {
            $id = $item['id'] ?? null;
            $record = $id ? $modelClass::query()->where('union_id', $union->id)->find($id) : null;

            if (! empty($item['delete'])) {
                $record?->delete();
                continue;
            }

            if ($this->isBlankRelatedRow($item)) {
                continue;
            }

            $data = $this->relatedData($item, $fields);
            $record ? $record->update($data) : $union->{$this->relationNameFor($modelClass)}()->create($data);
        }
    }

    /** @param array<int, array<string, mixed>> $commissions */
    private function syncCommissions(GuildUnion $union, array $commissions): void
    {
        foreach ($commissions as $item) {
            $commission = isset($item['id']) ? $union->commissions()->whereKey($item['id'])->first() : null;

            if (! empty($item['delete'])) {
                $commission?->delete();
                continue;
            }

            if ($this->isBlankRelatedRow($item) && empty($item['tasks'])) {
                continue;
            }

            $data = $this->relatedData($item, ['title', 'description', 'icon', 'sort_order', 'is_active']);
            $commission = $commission ? tap($commission)->update($data) : $union->commissions()->create($data);
            $this->syncCommissionTasks($commission, $item['tasks'] ?? []);
        }
    }

    /** @param array<int, array<string, mixed>> $tasks */
    private function syncCommissionTasks(UnionCommission $commission, array $tasks): void
    {
        foreach ($tasks as $item) {
            $task = isset($item['id']) ? $commission->tasks()->whereKey($item['id'])->first() : null;

            if (! empty($item['delete'])) {
                $task?->delete();
                continue;
            }

            if ($this->isBlankRelatedRow($item)) {
                continue;
            }

            $data = $this->relatedData($item, ['title', 'description', 'sort_order', 'is_active']);
            $task ? $task->update($data) : $commission->tasks()->create($data);
        }
    }

    /** @param array<string, mixed> $item @param array<int, string> $fields @return array<string, mixed> */
    private function relatedData(array $item, array $fields): array
    {
        $data = [];

        foreach ($fields as $field) {
            $value = $item[$field] ?? null;
            if (in_array($field, ['description'], true)) {
                $value = $this->sanitizeRichTextFields([$field => $value], [$field])[$field] ?? null;
            }
            if (in_array($field, ['sort_order'], true)) {
                $value = (int) ($value ?? 0);
            }
            if ($field === 'is_active') {
                $value = (bool) ($value ?? false);
            }
            if (in_array($field, ['meeting_date', 'updated_on'], true) && filled($value)) {
                $value = jalali_to_gregorian_datetime($value);
            }
            if (is_string($value)) {
                $value = trim($value);
            }
            $data[$field] = $value === '' ? null : $value;
        }

        return $data;
    }

    /** @param array<string, mixed> $item */
    private function isBlankRelatedRow(array $item): bool
    {
        return blank($item['title'] ?? null);
    }

    private function relationNameFor(string $modelClass): string
    {
        return match ($modelClass) {
            UnionRule::class => 'rules',
            UnionMinute::class => 'minutes',
            UnionEducation::class => 'educations',
            UnionPrice::class => 'prices',
        };
    }

    private function unionTypes()
    {
        return UnionType::query()->orderBy('sort_order')->orderBy('title')->get();
    }

    private function selectablePosts()
    {
        return Post::query()->published()->where('type', 'news')->orderByDesc('published_at')->orderBy('title')->take(200)->get(['id', 'title', 'published_at']);
    }

    private function unionCategories()
    {
        return Category::query()
            ->where(fn ($query) => $query->whereNull('type')->orWhere('type', 'union')->orWhere('type', 'union_type'))
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();
    }

    private function storeImage(Request $request, string $field, string $directory): ?string
    {
        return $this->uploadedOrSelectedImage($request, $field, $directory);
    }
}
