<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Dashboard;

use App\Actions\Dashboard\ArchiveConversationAction;
use App\Actions\Dashboard\ConversationStatsAction;
use App\Actions\Dashboard\LockConversationAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\DashboardConversationDetailsResource;
use App\Http\Resources\DashboardConversationResource;
use App\Http\Resources\MessageResource;
use App\Models\Conversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConversationManagementController extends Controller
{
    public function __construct(
        private readonly ConversationStatsAction $statsAction,
        private readonly ArchiveConversationAction $archiveAction,
        private readonly LockConversationAction $lockAction,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = Conversation::query()
            ->with([
                'participants',
                'lastMessage' => fn ($q) => $q->with('sender'),
            ])
            ->withCount('messages');

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('participants', fn ($q) => $q->where('name', 'like', "%{$search}%"));
        }

        $sortField = match ($request->input('sort_by', 'latest_activity')) {
            'created_at' => 'created_at',
            default => 'last_message_at',
        };

        $sortOrder = $request->input('sort_order', 'desc') === 'asc' ? 'asc' : 'desc';

        $query->orderBy($sortField, $sortOrder);

        $conversations = $query->paginate((int) $request->integer('per_page', 15));

        return response()->json(
            DashboardConversationResource::collection($conversations)
        );
    }

    public function stats(): JsonResponse
    {
        return response()->json([
            'data' => $this->statsAction->execute(),
        ]);
    }

    public function show(Request $request, Conversation $conversation): JsonResponse
    {
        $conversation->load('participants');

        $messages = $conversation->messages()
            ->with('sender')
            ->latest()
            ->paginate((int) $request->integer('per_page', 50));

        return response()->json([
            'data' => new DashboardConversationDetailsResource($conversation),
            'messages' => MessageResource::collection($messages),
        ]);
    }

    public function archive(Conversation $conversation): JsonResponse
    {
        $conversation = $this->archiveAction->execute($conversation);

        return response()->json([
            'message' => __('Conversation archived successfully.'),
            'data' => new DashboardConversationDetailsResource($conversation),
        ]);
    }

    public function lock(Conversation $conversation): JsonResponse
    {
        $conversation = $this->lockAction->execute($conversation);

        return response()->json([
            'message' => __('Conversation locked successfully.'),
            'data' => new DashboardConversationDetailsResource($conversation),
        ]);
    }
}
