<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Conversation\CreateConversationAction;
use App\Actions\Conversation\MarkConversationAsReadAction;
use App\Actions\Conversation\SendMessageAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Conversation\StoreConversationRequest;
use App\Http\Requests\Conversation\StoreMessageRequest;
use App\Http\Resources\ConversationDetailsResource;
use App\Http\Resources\ConversationResource;
use App\Http\Resources\MessageResource;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    public function __construct(
        private readonly CreateConversationAction $createConversationAction,
        private readonly SendMessageAction $sendMessageAction,
        private readonly MarkConversationAsReadAction $markConversationAsReadAction,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $conversations = Conversation::query()
            ->whereHas('participants', fn ($q) => $q->where('user_id', $user->id))
            ->whereHas('messages')
            ->with([
                'participants',
                'lastMessage' => fn ($q) => $q->with('sender'),
            ])
            ->withCount([
                'messages as unread_messages_count' => function ($q) use ($user) {
                    $q->where('sender_id', '!=', $user->id)
                        ->where('created_at', '>', function ($sub) use ($user) {
                            $sub->selectRaw("COALESCE(last_read_at, '1970-01-01')")
                                ->from('conversation_participants')
                                ->whereColumn('conversation_id', 'conversations.id')
                                ->where('user_id', $user->id)
                                ->limit(1);
                        });
                },
            ])
            ->orderBy('last_message_at', 'desc')
            ->paginate((int) $request->integer('per_page', 15));
        // info($conversations->toArray());

        return response()->json(
            ConversationResource::collection($conversations)
        );
    }

    public function store(StoreConversationRequest $request): JsonResponse
    {
        $conversation = $this->createConversationAction->execute(
            $request->user(),
            $request->validated(),
        );

        return response()->json([
            'message' => __('Conversation created successfully.'),
            'data' => new ConversationResource($conversation),
        ], 201);
    }

    public function show(Request $request, Conversation $conversation): JsonResponse
    {
        // $this->authorize('view', $conversation);

        $conversation->load('participants');

        $messages = $conversation->messages()
            ->with('sender')
            ->latest()
            ->paginate((int) $request->integer('per_page', 50));

        return response()->json([
            'data' => new ConversationDetailsResource($conversation),
            'messages' => MessageResource::collection($messages)->response()->getData(true),
        ]);
    }

    public function storeMessage(StoreMessageRequest $request, Conversation $conversation): JsonResponse
    {
        // $this->authorize('view', $conversation);

        $message = $this->sendMessageAction->execute(
            $conversation,
            $request->user(),
            $request->validated()['message'],
        );

        return response()->json([
            'message' => __('Message sent successfully.'),
            'data' => new MessageResource($message),
        ], 201);
    }

    public function markAsRead(Request $request, Conversation $conversation): JsonResponse
    {
        // $this->authorize('view', $conversation);

        $this->markConversationAsReadAction->execute(
            $conversation,
            $request->user(),
        );

        return response()->json([
            'message' => __('Conversation marked as read.'),
        ]);
    }

    public function findOrCreate(StoreConversationRequest $request): JsonResponse
    {
        $user = $request->user();

        $participantId = User::where('uuid', $request->validated('participant_ids')[0])->first()?->uuid;

        $conversation = Conversation::query()
            ->whereHas('participants', fn ($q) => $q->where('user_id', $user->id))
            ->whereHas('participants', fn ($q) => $q->where('user_id', $participantId))
            ->first();
        // info(['in', $user, $conversation]);
        if (! $conversation) {
            $conversation = $this->createConversationAction->execute(
                $user,
                [
                    'participant_ids' => [$participantId],
                    'type' => $request->validated('type'),
                ]
            );
        }

        return response()->json([
            'data' => new ConversationResource($conversation),
            'is_new' => $conversation->wasRecentlyCreated ?? false,
        ]);
    }
}
