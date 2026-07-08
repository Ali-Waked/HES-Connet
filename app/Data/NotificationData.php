<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\NotificationType;

final readonly class NotificationData
{
    public string $icon;

    public string $color;

    public string $group;

    public string $actionType;

    public function __construct(
        public NotificationType $type,
        public string $title,
        public string $message,
        public ?string $actionUrl = null,
        public ?string $entityUuid = null,
        public string $createdBy = 'system',
        ?string $icon = null,
        ?string $color = null,
        ?string $group = null,
        ?string $actionType = null,
    ) {
        $this->icon = $icon ?? $type->icon();
        $this->color = $color ?? $type->color();
        $this->group = $group ?? $type->group();
        $this->actionType = $actionType ?? $type->actionType();
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type->value,
            'title' => $this->title,
            'message' => $this->message,
            'icon' => $this->icon,
            'color' => $this->color,
            'group' => $this->group,
            'action_url' => $this->actionUrl,
            'action_type' => $this->actionType,
            'entity_uuid' => $this->entityUuid,
            'created_by' => $this->createdBy,
        ];
    }

    public static function fromArray(array $data): self
    {
        $type = NotificationType::fromEvent($data['type'] ?? 'system.broadcast');

        return new self(
            type: $type,
            title: $data['title'] ?? '',
            message: $data['message'] ?? '',
            actionUrl: $data['action_url'] ?? null,
            entityUuid: $data['entity_uuid'] ?? null,
            createdBy: $data['created_by'] ?? 'system',
            icon: $data['icon'] ?? null,
            color: $data['color'] ?? null,
            group: $data['group'] ?? null,
            actionType: $data['action_type'] ?? null,
        );
    }
}
