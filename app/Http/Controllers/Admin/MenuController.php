<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function index(): View
    {
        $menus = Menu::withCount('items')->latest()->paginate(15);

        return view('admin.menus.index', compact('menus'));
    }

    public function create(): View
    {
        return view('admin.menus.create', ['menu' => null, 'locations' => Menu::LOCATIONS]);
    }

    public function store(Request $request): RedirectResponse
    {
        $menu = Menu::create($this->validateMenu($request));

        return redirect()->route('admin.menus.show', $menu)->with('success', 'منو با موفقیت ایجاد شد.');
    }

    public function show(Menu $menu): View
    {
        $items = $menu->items()->whereNull('parent_id')->with('adminChildren')->get();

        return view('admin.menus.show', compact('menu', 'items'));
    }

    public function edit(Menu $menu): View
    {
        return view('admin.menus.edit', ['menu' => $menu, 'locations' => Menu::LOCATIONS]);
    }

    public function update(Request $request, Menu $menu): RedirectResponse
    {
        $menu->update($this->validateMenu($request));

        return redirect()->route('admin.menus.show', $menu)->with('success', 'منو با موفقیت ویرایش شد.');
    }

    public function destroy(Menu $menu): RedirectResponse
    {
        $menu->delete();

        return redirect()->route('admin.menus.index')->with('success', 'منو با موفقیت حذف شد.');
    }

    /** @return array{title: string, location: string, is_active: bool} */
    private function validateMenu(Request $request): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'location' => ['required', 'string', Rule::in(Menu::LOCATIONS)],
            'is_active' => ['required', 'boolean'],
        ]);

        return [
            'title' => $validated['title'],
            'location' => $validated['location'],
            'is_active' => (bool) $validated['is_active'],
        ];
    }
}
