<?php

namespace Database\Seeders;

use App\Models\GuildUnion;
use App\Models\InternalMessage;
use App\Models\User;
use Illuminate\Database\Seeder;

class InternalMessageSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->whereHas('roles', fn ($query) => $query->where('name', 'super-admin'))->first() ?: User::query()->first();
        $expert = User::query()->whereHas('roles', fn ($query) => $query->where('name', 'union-expert'))->first() ?: User::query()->whereKeyNot($admin?->id)->first();
        $union = GuildUnion::query()->first();

        if (! $admin || ! $expert) {
            return;
        }

        if ($union && ! $expert->union_id) {
            $expert->update(['union_id' => $union->id]);
        }

        $direct = InternalMessage::updateOrCreate(
            ['sender_id' => $admin->id, 'recipient_id' => $expert->id, 'subject' => 'هماهنگی امور اتحادیه'],
            [
                'body' => '<p>لطفاً آخرین وضعیت پرونده‌های اتحادیه را بررسی و نتیجه را اعلام کنید.</p>',
                'type' => InternalMessage::TYPE_DIRECT,
                'priority' => InternalMessage::PRIORITY_IMPORTANT,
                'allow_reply' => true,
                'sent_at' => now()->subDays(3),
                'meta' => ['send_type' => 'direct'],
            ]
        );

        InternalMessage::updateOrCreate(
            ['sender_id' => $admin->id, 'recipient_id' => $admin->id, 'subject' => 'اطلاعیه عمومی پنل'],
            [
                'body' => '<p>از همه کاربران درخواست می‌شود اطلاعات پروفایل خود را به‌روزرسانی کنند.</p>',
                'type' => InternalMessage::TYPE_BROADCAST,
                'priority' => InternalMessage::PRIORITY_NORMAL,
                'allow_reply' => false,
                'sent_at' => now()->subDays(2),
                'meta' => ['send_type' => 'broadcast', 'recipient_count' => User::query()->where('is_active', true)->count()],
            ]
        );

        if ($union) {
            InternalMessage::updateOrCreate(
                ['sender_id' => $admin->id, 'recipient_id' => $expert->id, 'subject' => 'پیام ویژه اتحادیه'],
                [
                    'body' => '<p>جلسه هماهنگی اتحادیه در هفته آینده برگزار خواهد شد.</p>',
                    'type' => InternalMessage::TYPE_UNION,
                    'priority' => InternalMessage::PRIORITY_NORMAL,
                    'allow_reply' => true,
                    'sent_at' => now()->subDay(),
                    'meta' => ['send_type' => 'union', 'union_id' => $union->id],
                ]
            );
        }

        InternalMessage::updateOrCreate(
            ['sender_id' => $expert->id, 'recipient_id' => $admin->id, 'parent_id' => $direct->id, 'subject' => 'پاسخ: هماهنگی امور اتحادیه'],
            [
                'body' => '<p>بررسی انجام شد و گزارش اولیه برای مدیرکل ارسال می‌شود.</p>',
                'type' => InternalMessage::TYPE_REPLY,
                'priority' => InternalMessage::PRIORITY_IMPORTANT,
                'allow_reply' => true,
                'sent_at' => now()->subHours(12),
                'meta' => ['source' => 'internal_reply'],
            ]
        );
    }
}
