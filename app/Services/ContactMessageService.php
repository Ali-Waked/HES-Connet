<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ContactMessage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ContactMessageService
{
    public function paginate(
        int $perPage = 15,
        ?string $search = null,
        ?string $status = null,
    ): LengthAwarePaginator {
        return ContactMessage::query()
            ->when(
                $search,
                fn ($query) => $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('message', 'like', "%{$search}%");
                })
            )
            ->when(
                $status,
                fn ($query) => $query->where('status', $status)
            )
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): ContactMessage
    {
        return ContactMessage::create($data);
    }

    public function markAsRead(ContactMessage $contactMessage): ContactMessage
    {
        if ($contactMessage->status === 'new') {
            $contactMessage->update(['status' => 'read']);
        }

        return $contactMessage->refresh();
    }

    public function updateStatus(ContactMessage $contactMessage, string $status): ContactMessage
    {
        $contactMessage->update(['status' => $status]);

        return $contactMessage->refresh();
    }
}
