<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notifications = $request->user()
            ->notifications()
            ->paginate((int) $request->integer('per_page', 20));

        return response()->json([
            'success' => true,
            'message' => 'Notifications retrieved successfully.',
            'data' => [
                'notifications' => NotificationResource::collection($notifications),
                'unread_count' => $request->user()->unreadNotifications()->count(),
            ],
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
            ],
        ]);
    }

    public function unread(Request $request): JsonResponse
    {
        $notifications = $request->user()
            ->unreadNotifications()
            ->paginate((int) $request->integer('per_page', 20));

        return response()->json([
            'success' => true,
            'message' => 'Unread notifications retrieved successfully.',
            'data' => [
                'notifications' => NotificationResource::collection($notifications),
                'unread_count' => $notifications->total(),
            ],
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
            ],
        ]);
    }

    public function count(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Unread count retrieved successfully.',
            'data' => [
                'unread_count' => $request->user()->unreadNotifications()->count(),
            ],
        ]);
    }

    public function read(Request $request, string $notification): JsonResponse
    {
        $notif = $request->user()->notifications()->findOrFail($notification);

        $notif->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read.',
            'data' => [
                'notification' => new NotificationResource($notif->fresh()),
            ],
        ]);
    }

    public function readAll(Request $request): JsonResponse
    {
        $count = $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read.',
            'data' => [
                'marked_read_count' => $count,
            ],
        ]);
    }

    public function destroy(Request $request, string $notification): JsonResponse
    {
        $notif = $request->user()->notifications()->findOrFail($notification);

        $notif->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted successfully.',
            'data' => [],
        ]);
    }

    public function destroyAll(Request $request): JsonResponse
    {
        $count = $request->user()->notifications()->delete();

        return response()->json([
            'success' => true,
            'message' => 'All notifications deleted successfully.',
            'data' => [
                'deleted_count' => $count,
            ],
        ]);
    }
}
