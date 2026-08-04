<?php

namespace Tests\Feature\Admin;

use App\Models\InternalMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InternalMessageAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_admin_user_can_open_own_inbox_without_messages_view_permission(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $response = $this->actingAs($user)->get(route('admin.messages.inbox'));

        $response->assertOk();
    }

    public function test_authenticated_admin_user_can_open_own_sent_messages_without_messages_view_permission(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $response = $this->actingAs($user)->get(route('admin.messages.sent'));

        $response->assertOk();
    }

    public function test_authenticated_admin_user_can_open_own_message_without_messages_view_permission(): void
    {
        $sender = User::factory()->create(['is_active' => true]);
        $recipient = User::factory()->create(['is_active' => true]);

        $message = InternalMessage::create([
            'sender_id' => $sender->id,
            'recipient_id' => $recipient->id,
            'subject' => 'پیام تست',
            'body' => 'متن پیام تست',
            'type' => InternalMessage::TYPE_DIRECT,
            'priority' => InternalMessage::PRIORITY_NORMAL,
            'allow_reply' => true,
            'sent_at' => now(),
        ]);

        $response = $this->actingAs($recipient)->get(route('admin.messages.show', $message));

        $response->assertOk();
        $this->assertNotNull($message->fresh()->read_at);
    }

    public function test_authenticated_admin_user_cannot_open_another_users_message_without_manage_permission(): void
    {
        $sender = User::factory()->create(['is_active' => true]);
        $recipient = User::factory()->create(['is_active' => true]);
        $otherUser = User::factory()->create(['is_active' => true]);

        $message = InternalMessage::create([
            'sender_id' => $sender->id,
            'recipient_id' => $recipient->id,
            'subject' => 'پیام تست',
            'body' => 'متن پیام تست',
            'type' => InternalMessage::TYPE_DIRECT,
            'priority' => InternalMessage::PRIORITY_NORMAL,
            'allow_reply' => true,
            'sent_at' => now(),
        ]);

        $response = $this->actingAs($otherUser)->get(route('admin.messages.show', $message));

        $response->assertForbidden();
    }
}
