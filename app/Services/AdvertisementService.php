<?php

namespace App\Services;

use App\Models\Advertisement;
use Illuminate\Database\Eloquent\Collection;

class AdvertisementService
{
    public function getByPosition(string $positionKey, ?int $limit = null): Collection
    {
        $query = Advertisement::query()
            ->displayable()
            ->with('position')
            ->whereHas('position', fn ($query) => $query->where('key', $positionKey))
            ->orderBy('sort_order')
            ->latest('starts_at');

        if ($limit !== null) {
            $query->limit($limit);
        }

        return $query->get();
    }

    public function getDisplayable(?int $limit = null): Collection
    {
        $query = Advertisement::query()
            ->displayable()
            ->with('position')
            ->orderBy('sort_order')
            ->latest('starts_at');

        if ($limit !== null) {
            $query->limit($limit);
        }

        return $query->get();
    }
}
