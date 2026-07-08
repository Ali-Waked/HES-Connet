<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Data\NotificationData;
use App\Enums\NotificationType;
use App\Models\User;
use App\Notifications\DatabaseNotification as DatabaseNotificationClass;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    private function createNotificationForUser(User $user, bool $read = false): DatabaseNotification
    {
        $data = new NotificationData(
            type: NotificationType::APPOINTMENT_CREATED,
            title: 'Test Notification',
            message: 'This is a test notification.',
            actionUrl: '/appointments/123',
            entityUuid: 'entity-uuid-123',
        );

        $notification = $user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => DatabaseNotificationClass::class,
            'data' => $data->toArray(),
            'read_at' => $read ? now() : null,
        ]);

        return $notification;
    }

    public function test_can_list_notifications(): void
    {
        $this->createNotificationForUser($this->user);
        $this->createNotificationForUser($this->user);

        $response = $this->actingAs($this->user)->getJson('/api/notifications');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'notifications' => [
                        '*' => [
                            'id', 'type', 'title', 'message', 'icon',
                            'color', 'group', 'action_url', 'action_type',
                            'entity_uuid', 'read_at', 'created_at',
                        ],
                    ],
                    'unread_count',
                ],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);

        $this->assertCount(2, $response->json('data.notifications'));
        $this->assertEquals(2, $response->json('data.unread_count'));
    }

    public function test_list_notifications_returns_empty_when_none(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/notifications');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'notifications' => [],
                    'unread_count' => 0,
                ],
            ]);
    }

    public function test_list_notifications_requires_authentication(): void
    {
        $response = $this->getJson('/api/notifications');
        $response->assertStatus(401);
    }

    public function test_can_list_unread_notifications(): void
    {
        $this->createNotificationForUser($this->user, read: true);
        $this->createNotificationForUser($this->user, read: false);

        $response = $this->actingAs($this->user)->getJson('/api/notifications/unread');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data.notifications'));
        $this->assertEquals(1, $response->json('data.unread_count'));
    }

    public function test_can_get_unread_count(): void
    {
        $this->createNotificationForUser($this->user);
        $this->createNotificationForUser($this->user);
        $this->createNotificationForUser($this->user, read: true);

        $response = $this->actingAs($this->user)->getJson('/api/notifications/count');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => ['unread_count' => 2],
            ]);
    }

    public function test_can_mark_notification_as_read(): void
    {
        $notification = $this->createNotificationForUser($this->user);

        $response = $this->actingAs($this->user)
            ->patchJson('/api/notifications/'.$notification->id.'/read');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Notification marked as read.',
            ]);

        $this->assertNotNull($response->json('data.notification.read_at'));
    }

    public function test_cannot_mark_other_users_notification_as_read(): void
    {
        $otherUser = User::factory()->create();
        $notification = $this->createNotificationForUser($otherUser);

        $response = $this->actingAs($this->user)
            ->patchJson('/api/notifications/'.$notification->id.'/read');

        $response->assertStatus(404);
    }

    public function test_can_mark_all_notifications_as_read(): void
    {
        $this->createNotificationForUser($this->user);
        $this->createNotificationForUser($this->user);
        $this->createNotificationForUser($this->user);

        $response = $this->actingAs($this->user)->patchJson('/api/notifications/read-all');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'All notifications marked as read.',
                'data' => ['marked_read_count' => 3],
            ]);

        $this->assertEquals(0, $this->user->fresh()->unreadNotifications()->count());
    }

    public function test_can_delete_notification(): void
    {
        $notification = $this->createNotificationForUser($this->user);

        $response = $this->actingAs($this->user)
            ->deleteJson('/api/notifications/'.$notification->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Notification deleted successfully.',
            ]);

        $this->assertEquals(0, $this->user->notifications()->count());
    }

    public function test_cannot_delete_other_users_notification(): void
    {
        $otherUser = User::factory()->create();
        $notification = $this->createNotificationForUser($otherUser);

        $response = $this->actingAs($this->user)
            ->deleteJson('/api/notifications/'.$notification->id);

        $response->assertStatus(404);
    }

    public function test_can_delete_all_notifications(): void
    {
        $this->createNotificationForUser($this->user);
        $this->createNotificationForUser($this->user);
        $this->createNotificationForUser($this->user);

        $response = $this->actingAs($this->user)->deleteJson('/api/notifications');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'All notifications deleted successfully.',
                'data' => ['deleted_count' => 3],
            ]);

        $this->assertEquals(0, $this->user->notifications()->count());
    }

    public function test_notification_service_creates_database_notification(): void
    {
        $service = app(NotificationService::class);

        $service->notify(
            $this->user,
            NotificationType::APPOINTMENT_CREATED,
            'Test Title',
            'Test Message',
            '/test-url',
            'entity-uuid',
        );

        $this->assertEquals(1, $this->user->notifications()->count());

        $notification = $this->user->notifications()->first();
        $data = $notification->data;

        $this->assertEquals('appointment.created', $data['type']);
        $this->assertEquals('Test Title', $data['title']);
        $this->assertEquals('Test Message', $data['message']);
        $this->assertEquals('/test-url', $data['action_url']);
        $this->assertEquals('entity-uuid', $data['entity_uuid']);
        $this->assertEquals('calendar-plus', $data['icon']);
    }

    public function test_notification_data_dto_defaults(): void
    {
        $data = new NotificationData(
            type: NotificationType::APPOINTMENT_CONFIRMED,
            title: 'Confirmed',
            message: 'Done',
        );

        $this->assertEquals('calendar-check', $data->icon);
        $this->assertEquals('success', $data->color);
        $this->assertEquals('appointments', $data->group);
        $this->assertEquals('appointment', $data->actionType);
        $this->assertEquals('system', $data->createdBy);
    }

    public function test_notification_type_enum_metadata(): void
    {
        $this->assertEquals('calendar-check', NotificationType::APPOINTMENT_CONFIRMED->icon());
        $this->assertEquals('success', NotificationType::APPOINTMENT_CONFIRMED->color());
        $this->assertEquals('appointments', NotificationType::APPOINTMENT_CONFIRMED->group());

        $this->assertEquals('user-x', NotificationType::DOCTOR_REJECTED->icon());
        $this->assertEquals('danger', NotificationType::DOCTOR_REJECTED->color());
        $this->assertEquals('staff', NotificationType::DOCTOR_REJECTED->group());

        $this->assertEquals('settings', NotificationType::MAINTENANCE_NOTICE->icon());
        $this->assertEquals('warning', NotificationType::MAINTENANCE_NOTICE->color());
        $this->assertEquals('system', NotificationType::MAINTENANCE_NOTICE->group());
    }

    public function test_notification_type_from_event(): void
    {
        $this->assertEquals(
            NotificationType::APPOINTMENT_CREATED,
            NotificationType::fromEvent('appointment.created'),
        );

        $this->assertEquals(
            NotificationType::BROADCAST_NOTIFICATION,
            NotificationType::fromEvent('unknown.event'),
        );
    }
}
