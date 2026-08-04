<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\Commission;
use App\Models\CommissionSession;
use App\Models\ElectronicService;
use App\Models\Gallery;
use App\Models\InternalMessage;
use App\Models\Page;
use App\Models\Post;
use App\Models\System as SystemModel;
use App\Models\TourismPlace;
use App\Models\User;
use App\Models\Video;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ContentApprovalService
{
    public const STATUSES = ['draft', 'pending', 'approved', 'rejected', 'published', 'archived'];
    public const LIMITED_STATUSES = ['draft', 'pending'];

    /**
     * @return array<string, array<string, mixed>>
     */
    public function contentTypes(): array
    {
        return collect([
            'pages' => [
                'model' => Page::class,
                'label' => 'صفحات',
                'permission' => 'pages.approve',
                'publish_permission' => 'pages.publish',
                'show_route' => 'admin.pages.show',
                'title' => 'title',
                'summary' => ['excerpt', 'body', 'content'],
            ],
            'posts' => [
                'model' => Post::class,
                'label' => 'اخبار',
                'permission' => 'posts.approve',
                'publish_permission' => 'posts.publish',
                'show_route' => 'admin.posts.show',
                'title' => 'title',
                'summary' => ['excerpt', 'summary', 'body'],
                'image' => 'featured_image',
            ],
            'announcements' => [
                'model' => Announcement::class,
                'label' => 'اطلاعیه‌ها',
                'permission' => 'announcements.approve',
                'publish_permission' => 'announcements.publish',
                'show_route' => 'admin.announcements.show',
                'title' => 'title',
                'summary' => ['summary', 'body', 'content'],
                'image' => 'image',
            ],
            'galleries' => [
                'model' => Gallery::class,
                'label' => 'گالری‌ها',
                'permission' => 'galleries.approve',
                'publish_permission' => 'galleries.publish',
                'show_route' => 'admin.galleries.show',
                'title' => 'title',
                'summary' => ['description'],
                'image' => 'cover_image',
            ],
            'videos' => [
                'model' => Video::class,
                'label' => 'ویدیوها',
                'permission' => 'videos.approve',
                'publish_permission' => 'videos.publish',
                'show_route' => 'admin.videos.show',
                'title' => 'title',
                'summary' => ['description'],
                'image' => 'cover_image',
            ],
            'tourism' => [
                'model' => TourismPlace::class,
                'label' => 'گردشگری',
                'permission' => 'tourism.approve',
                'publish_permission' => 'tourism.publish',
                'show_route' => 'admin.tourism.show',
                'title' => 'title',
                'summary' => ['short_description', 'description'],
                'image' => 'featured_image',
            ],
            'commissions' => [
                'model' => Commission::class,
                'label' => 'کمیسیون‌ها',
                'permission' => 'commissions.approve',
                'publish_permission' => 'commissions.publish',
                'show_route' => 'admin.commissions.show',
                'title' => 'title',
                'summary' => ['description'],
                'image' => 'image',
            ],
            'commission_sessions' => [
                'model' => CommissionSession::class,
                'label' => 'جلسات کمیسیون',
                'permission' => 'commissions.approve',
                'publish_permission' => 'commissions.publish',
                'show_route' => fn (CommissionSession $session) => route('admin.commissions.sessions.show', [$session->commission, $session]),
                'title' => 'title',
                'summary' => ['description'],
            ],
            'systems' => [
                'model' => SystemModel::class,
                'label' => 'سامانه‌ها',
                'permission' => 'systems.approve',
                'publish_permission' => 'systems.publish',
                'show_route' => 'admin.systems.show',
                'title' => 'title',
                'summary' => ['short_description', 'description'],
                'image' => 'image',
            ],
            'electronic_services' => [
                'model' => ElectronicService::class,
                'label' => 'خدمات الکترونیک',
                'permission' => 'electronic_services.approve',
                'publish_permission' => 'electronic_services.publish',
                'show_route' => 'admin.electronic_services.show',
                'title' => 'title',
                'summary' => ['short_description', 'body'],
                'image' => 'image',
            ],
            'services' => [
                'model' => 'App\\Models\\Service',
                'label' => 'خدمات',
                'permission' => 'services.approve',
                'publish_permission' => 'services.publish',
                'show_route' => 'admin.services.show',
                'title' => 'title',
                'summary' => ['short_description', 'description'],
                'image' => 'image',
            ],
            'congratulation_messages' => [
                'model' => 'App\\Models\\CongratulationMessage',
                'label' => 'پیام‌های تبریک',
                'permission' => 'congratulation_messages.approve',
                'publish_permission' => 'congratulation_messages.publish',
                'show_route' => 'admin.congratulation_messages.show',
                'title' => 'title',
                'summary' => ['message', 'description'],
                'image' => 'image',
            ],
        ])->filter(fn (array $definition) => class_exists($definition['model']))->all();
    }

    public function definition(string $type): array
    {
        $definition = $this->contentTypes()[$type] ?? null;
        abort_if(! $definition, 404);

        return $definition;
    }

    public function find(string $type, int $id): Model
    {
        /** @var class-string<Model> $model */
        $model = $this->definition($type)['model'];

        return $model::query()->findOrFail($id);
    }

    public function ensureCanModerate(User $user, string $type, string $action = 'approve'): void
    {
        $definition = $this->definition($type);
        $permissions = [$definition['permission']];

        if (in_array($action, ['publish', 'archive'], true)) {
            $permissions[] = $definition['publish_permission'] ?? $definition['permission'];
        }

        abort_unless($user->hasAnyPermission(array_values(array_unique($permissions))), 403);
    }

    /**
     * @return array<int, string>
     */
    public function allowedStatusesFor(?User $user, string|array|null $permissions = null): array
    {
        $permissions = is_array($permissions) ? $permissions : array_filter([$permissions]);

        if ($user && $permissions !== [] && $user->hasAnyPermission($permissions)) {
            return self::STATUSES;
        }

        return self::LIMITED_STATUSES;
    }

    public function registerPendingNotificationListeners(): void
    {
        foreach ($this->contentTypes() as $type => $definition) {
            /** @var class-string<Model> $model */
            $model = $definition['model'];

            $model::created(function (Model $item) use ($type): void {
                if ($item->getAttribute('status') === 'pending') {
                    $this->notifyModeratorsOfPending($item, $type);
                }
            });

            $model::updated(function (Model $item) use ($type): void {
                if ($item->wasChanged('status') && $item->getAttribute('status') === 'pending') {
                    $this->notifyModeratorsOfPending($item, $type);
                }
            });
        }
    }

    public function notifyModeratorsOfPending(Model $model, string $type): void
    {
        $definition = $this->definition($type);
        $permissions = array_values(array_unique(array_filter([
            'pending_approvals.view',
            $definition['permission'] ?? null,
            $definition['publish_permission'] ?? null,
        ])));

        $moderators = User::query()
            ->where('is_active', true)
            ->where(function ($query) use ($permissions): void {
                $query->whereHas('roles', fn ($roleQuery) => $roleQuery->where('name', 'super-admin'))
                    ->orWhereHas('roles.permissions', fn ($permissionQuery) => $permissionQuery->whereIn('name', $permissions));
            })
            ->get();

        if ($moderators->isEmpty()) {
            return;
        }

        $title = (string) $model->getAttribute($definition['title']);
        $creatorId = $model->getAttribute('created_by');
        $showUrl = $this->showUrl($model, $definition);
        $body = 'محتوای جدید «'.e($title).'» در بخش '.$definition['label'].' ثبت شده و در انتظار تایید و انتشار است.';

        if ($showUrl) {
            $body .= '<br><a href="'.e($showUrl).'">مشاهده محتوا</a>';
        }

        foreach ($moderators as $moderator) {
            $alreadyNotified = InternalMessage::query()
                ->where('recipient_id', $moderator->id)
                ->where('subject', 'محتوای جدید در انتظار انتشار')
                ->where('type', InternalMessage::TYPE_DIRECT)
                ->where('meta->content_type', $type)
                ->where('meta->content_id', $model->getKey())
                ->exists();

            if ($alreadyNotified) {
                continue;
            }

            InternalMessage::query()->create([
                'sender_id' => $creatorId,
                'recipient_id' => $moderator->id,
                'subject' => 'محتوای جدید در انتظار انتشار',
                'body' => $body,
                'type' => InternalMessage::TYPE_DIRECT,
                'priority' => InternalMessage::PRIORITY_IMPORTANT,
                'allow_reply' => false,
                'sent_at' => now(),
                'meta' => [
                    'content_type' => $type,
                    'content_id' => $model->getKey(),
                ],
            ]);
        }
    }

    public function approve(Model $model, User $user): Model
    {
        return $this->updateStatus($model, [
            'status' => 'approved',
            'approved_by' => $user->id,
            'rejected_reason' => null,
        ]);
    }

    public function reject(Model $model, User $user, string $reason): Model
    {
        return $this->updateStatus($model, [
            'status' => 'rejected',
            'approved_by' => $user->id,
            'rejected_reason' => $reason,
        ]);
    }

    public function publish(Model $model, User $user): Model
    {
        return $this->updateStatus($model, [
            'status' => 'published',
            'approved_by' => $user->id,
            'published_at' => $model->getAttribute('published_at') ?: now(),
            'rejected_reason' => null,
        ]);
    }

    public function archive(Model $model, User $user): Model
    {
        return $this->updateStatus($model, ['status' => 'archived']);
    }

    public function updateStatus(Model $model, array $attributes): Model
    {
        $table = $model->getTable();
        $attributes = collect($attributes)
            ->filter(fn ($value, string $column) => Schema::hasColumn($table, $column))
            ->all();

        $model->forceFill($attributes)->save();

        return $model->refresh();
    }

    public function pendingItems(?int $limit = null): Collection
    {
        $items = collect();

        foreach ($this->contentTypes() as $type => $definition) {
            /** @var class-string<Model> $model */
            $model = $definition['model'];
            $query = $model::query()->where('status', 'pending')->latest();

            if ($type === 'commission_sessions') {
                $query->with('commission');
            }

            $query->get()->each(function (Model $item) use ($items, $type, $definition): void {
                $items->push([
                    'type' => $type,
                    'label' => $definition['label'],
                    'model' => $item,
                    'title' => (string) $item->getAttribute($definition['title']),
                    'summary' => $this->summary($item, $definition),
                    'image' => $this->image($item, $definition),
                    'show_url' => $this->showUrl($item, $definition),
                    'created_at' => $item->getAttribute('created_at'),
                ]);
            });
        }

        $items = $items->sortByDesc('created_at')->values();

        return $limit ? $items->take($limit)->values() : $items;
    }

    public static function statusLabels(): array
    {
        return [
            'draft' => 'پیش‌نویس',
            'pending' => 'در انتظار تایید',
            'approved' => 'تایید شده',
            'rejected' => 'رد شده',
            'published' => 'منتشر شده',
            'archived' => 'آرشیو شده',
        ];
    }

    private function summary(Model $item, array $definition): string
    {
        foreach (Arr::wrap($definition['summary'] ?? []) as $field) {
            $value = trim(strip_tags((string) $item->getAttribute($field)));
            if ($value !== '') {
                return Str::limit($value, 160);
            }
        }

        return 'بدون توضیح';
    }

    private function image(Model $item, array $definition): ?string
    {
        $field = $definition['image'] ?? null;
        $image = $field ? $item->getAttribute($field) : null;

        return $image ? asset('storage/'.$image) : null;
    }

    private function showUrl(Model $item, array $definition): ?string
    {
        $showRoute = $definition['show_route'] ?? null;

        if (is_callable($showRoute)) {
            return $showRoute($item);
        }

        return $showRoute && Route::has($showRoute) ? route($showRoute, $item) : null;
    }
}
