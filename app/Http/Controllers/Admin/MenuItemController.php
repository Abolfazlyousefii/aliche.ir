<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MenuItemController extends Controller
{
    public function create(Menu $menu): View
    {
        return view('admin.menus.items.create', $this->formData($menu));
    }

    public function store(Request $request, Menu $menu): RedirectResponse
    {
        $menu->items()->create($this->validateItem($request, $menu));

        return redirect()->route('admin.menus.show', $menu)->with('success', 'آیتم منو با موفقیت اضافه شد.');
    }

    public function edit(Menu $menu, MenuItem $item): View
    {
        $this->ensureItemBelongsToMenu($menu, $item);

        return view('admin.menus.items.edit', $this->formData($menu, $item));
    }

    public function update(Request $request, Menu $menu, MenuItem $item): RedirectResponse
    {
        $this->ensureItemBelongsToMenu($menu, $item);
        $item->update($this->validateItem($request, $menu, $item));

        return redirect()->route('admin.menus.show', $menu)->with('success', 'آیتم منو با موفقیت ویرایش شد.');
    }

    public function destroy(Menu $menu, MenuItem $item): RedirectResponse
    {
        $this->ensureItemBelongsToMenu($menu, $item);
        $item->delete();

        return redirect()->route('admin.menus.show', $menu)->with('success', 'آیتم منو با موفقیت حذف شد.');
    }

    public function toggle(Menu $menu, MenuItem $item): RedirectResponse
    {
        $this->ensureItemBelongsToMenu($menu, $item);
        $item->update(['is_active' => ! $item->is_active]);

        return redirect()->route('admin.menus.show', $menu)->with('success', 'وضعیت آیتم تغییر کرد.');
    }

    public function sort(Request $request, Menu $menu): JsonResponse
    {
        $validated = $request->validate([
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'integer', 'exists:menu_items,id'],
            'items.*.children' => ['nullable', 'array'],
        ]);

        $this->syncSort($menu, $validated['items']);

        return response()->json(['message' => 'ترتیب منو ذخیره شد.']);
    }

    /** @return array<string, mixed> */
    private function formData(Menu $menu, ?MenuItem $item = null): array
    {
        return [
            'menu' => $menu,
            'item' => $item,
            'types' => MenuItem::TYPES,
            'targets' => MenuItem::TARGETS,
            'parents' => $menu->items()
                ->when($item, fn ($query) => $query->where('id', '!=', $item->id))
                ->orderBy('sort_order')
                ->orderBy('title')
                ->get(),
            'internalRoutes' => $this->internalRoutes(),
        ];
    }

    /** @return array<string, mixed> */
    private function validateItem(Request $request, Menu $menu, ?MenuItem $item = null): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'link_mode' => ['required', Rule::in(['manual', 'internal', 'content'])],
            'type' => ['nullable', 'required_if:link_mode,content', 'string', Rule::in(MenuItem::TYPES)],
            'url' => ['nullable', 'required_if:link_mode,manual', 'string', 'max:255'],
            'route_name' => ['nullable', 'required_if:link_mode,internal', 'string', Rule::in(array_keys($this->internalRoutes()))],
            'reference_type' => ['nullable', 'string', 'max:150'],
            'reference_id' => ['nullable', 'integer', 'min:1'],
            'parent_id' => ['nullable', 'integer', Rule::exists('menu_items', 'id')->where('menu_id', $menu->id)],
            'target' => ['required', 'string', Rule::in(MenuItem::TARGETS)],
            'icon' => ['nullable', 'string', 'max:80'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ]);

        if ($item && (int) ($validated['parent_id'] ?? 0) === $item->id) {
            $validated['parent_id'] = null;
        }

        if ($validated['link_mode'] === 'manual') {
            $validated['type'] = 'custom';
            $validated['route_name'] = null;
            $validated['reference_type'] = null;
            $validated['reference_id'] = null;
        } elseif ($validated['link_mode'] === 'internal') {
            $validated['type'] = 'custom';
            $validated['url'] = null;
            $validated['reference_type'] = null;
            $validated['reference_id'] = null;
        } else {
            $validated['route_name'] = null;
        }

        unset($validated['link_mode']);

        return $validated;
    }

    /** @return array<string, string> */
    private function internalRoutes(): array
    {
        return [
            'home' => 'صفحه اصلی',
            'posts.index' => 'آرشیو اخبار',
            'tourism.index' => 'گردشگری',
            'latest_posts' => 'اخبار جدید',
            'daily_news' => 'اخبار روزانه',
            'important_news' => 'خبرهای مهم',
            'featured_news' => 'خبرهای ویژه',
            'announcements.index' => 'اطلاعیه‌ها',
            'galleries.index' => 'گالری تصاویر',
            'videos.index' => 'ویدیوها',
            'systems.index' => 'سامانه‌ها',
            'electronic-services.index' => 'خدمات الکترونیک',
            'commissions.index' => 'کمیسیون‌ها',
            'contact.create' => 'تماس با ما',
        ];
    }

    /** @param array<int, array{id: int, children?: array<int, mixed>}> $items */
    private function syncSort(Menu $menu, array $items, ?int $parentId = null): void
    {
        foreach ($items as $index => $itemData) {
            MenuItem::query()
                ->where('menu_id', $menu->id)
                ->where('id', $itemData['id'])
                ->update(['parent_id' => $parentId, 'sort_order' => $index + 1]);

            if (! empty($itemData['children']) && is_array($itemData['children'])) {
                $this->syncSort($menu, $itemData['children'], (int) $itemData['id']);
            }
        }
    }

    private function ensureItemBelongsToMenu(Menu $menu, MenuItem $item): void
    {
        abort_unless($item->menu_id === $menu->id, 404);
    }
}
