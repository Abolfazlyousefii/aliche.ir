<?php

namespace App\Observers;

use App\Models\Post;
use App\Services\DailyNewsService;

class PostObserver
{
    public function saved(Post $post): void { $this->flush($post); }
    public function deleted(Post $post): void { $this->flush($post); }

    private function flush(Post $post): void
    {
        $service = app(DailyNewsService::class);
        $service->forgetFor($post->published_at?->copy()->startOfDay());
        $original = $post->getOriginal('published_at');
        $service->forgetFor($original ? \Carbon\Carbon::parse($original)->startOfDay() : null);
    }
}
