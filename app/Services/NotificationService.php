<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\NotificationData;
use App\Enums\NotificationType;
use App\Models\User;
use App\Notifications\DatabaseNotification;
use App\Services\Notification\NotificationLogService;
use Illuminate\Database\Eloquent\Collection;

class NotificationService
{
    public function __construct(
        private readonly NotificationLogService $logService,
    ) {}

    public function notify(
        User|Collection|array $notifiables,
        NotificationType $type,
        string $title,
        string $message,
        ?string $actionUrl = null,
        ?string $entityUuid = null,
        string $createdBy = 'system',
    ): void {
        $data = new NotificationData(
            type: $type,
            title: $title,
            message: $message,
            actionUrl: $actionUrl,
            entityUuid: $entityUuid,
            createdBy: $createdBy,
        );

        $notifiables = is_array($notifiables) ? $notifiables : [$notifiables];

        foreach ($notifiables as $notifiable) {
            if ($notifiable instanceof User) {
                $notifiable->notify(new DatabaseNotification($data));
                $this->logService->markSent($type->value, $notifiable->id, 'database');
            }
        }
    }

    public function notifyAdmins(
        NotificationType $type,
        string $title,
        string $message,
        ?string $actionUrl = null,
        ?string $entityUuid = null,
    ): void {
        $admins = User::whereHas('systemRoles', fn ($q) => $q->where('slug', 'super_admin'))->get();

        $this->notify($admins, $type, $title, $message, $actionUrl, $entityUuid, 'system');
    }

    public function notifyFacilityAdmins(
        int $facilityId,
        NotificationType $type,
        string $title,
        string $message,
        ?string $actionUrl = null,
        ?string $entityUuid = null,
    ): void {
        $admins = User::whereHas('staff.facilityStaff', fn ($q) => $q
            ->where('facility_id', $facilityId)
            ->whereNull('ended_at')
            ->whereHas('role', fn ($r) => $r->where('slug', 'facility_admin'))
        )->get();

        $this->notify($admins, $type, $title, $message, $actionUrl, $entityUuid, 'system');
    }
}
