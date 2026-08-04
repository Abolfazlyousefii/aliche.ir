<?php

namespace App\Services;

use App\Models\Menu;
use Illuminate\Support\Collection;

class MenuService
{
    /**
     * @return Collection<int, \App\Models\MenuItem>
     */
    public function items(string $location = 'main'): Collection
    {
        $menu = Menu::query()
            ->where('location', $location)
            ->where('is_active', true)
            ->with(['rootItems' => fn ($query) => $query->where('is_active', true)])
            ->latest('id')
            ->first();

        return $menu?->rootItems ?? collect();
    }
}
