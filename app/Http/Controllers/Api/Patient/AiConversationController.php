<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Patient;

use App\Http\Controllers\Controller;
use App\Http\Requests\Conversation\StoreConversationRequest;
use App\Http\Requests\Conversation\StoreMessageRequest;
use App\Http\Resources\ConversationResource;
use App\Http\Resources\MessageResource;
use App\Models\AiMedicalConversation;
use App\Services\AiConversationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiConversationController extends Controller
{
    public function __construct(
        private readonly AiConversationService $conversationService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $conversations = $this->conversationService->listConversations($request->user()->id);

        return response()->json([
            'data' => ConversationResource::collection($conversations),
            'meta' => [
                'current_page' => $conversations->currentPage(),
                'last_page' => $conversations->lastPage(),
                'total' => $conversations->total(),
            ],
        ]);
    }

    public function store(StoreConversationRequest $request): JsonResponse
    {
        $result = $this->conversationService->createConversation(
            userId: $request->user()->id,
            title: $request->validated('title'),
            message: $request->validated('message'),
        );

        if ($result instanceof AiMedicalConversation) {
            return response()->json([
                'data' => new ConversationResource($result),
            ], 201);
        }

        if (isset($result['requires_new_conversation'])) {
            return response()->json($result, 403);
        }

        return response()->json([
            'data' => [
                'conversation' => new ConversationResource($result['conversation']),
                'user_message' => new MessageResource($result['user_message']),
                'assistant_message' => new MessageResource($result['assistant_message']),
            ],
        ], 201);
    }

    public function show(Request $request, string $uuid): JsonResponse
    {
        $conversation = $this->conversationService->getConversation($uuid, $request->user()->id);

        $messages = $this->conversationService->getMessages($conversation);

        return response()->json([
            'data' => [
                'conversation' => new ConversationResource($conversation),
                'messages' => [
                    'data' => MessageResource::collection($messages),
                    'meta' => [
                        'current_page' => $messages->currentPage(),
                        'last_page' => $messages->lastPage(),
                        'total' => $messages->total(),
                    ],
                ],
            ],
        ]);
    }

    public function update(StoreConversationRequest $request, string $uuid): JsonResponse
    {
        $conversation = $this->conversationService->getConversation($uuid, $request->user()->id);

        $conversation->update([
            'title' => $request->validated('title', $conversation->title),
        ]);

        return response()->json([
            'data' => new ConversationResource($conversation->fresh()),
        ]);
    }

    public function destroy(Request $request, string $uuid): JsonResponse
    {
        $conversation = $this->conversationService->getConversation($uuid, $request->user()->id);

        $this->conversationService->deleteConversation($conversation);

        return response()->json(['message' => 'Conversation deleted.']);
    }

    public function storeMessage(StoreMessageRequest $request, string $uuid): JsonResponse
    {
        $conversation = $this->conversationService->getConversation($uuid, $request->user()->id);

        $result = $this->conversationService->respond(
            conversation: $conversation,
            userMessage: $request->validated('message'),
        );

        if (isset($result['requires_new_conversation'])) {
            return response()->json([
                'requires_new_conversation' => true,
                'reason' => $result['reason'],
            ], 403);
        }

        return response()->json([
            'data' => [
                'conversation' => new ConversationResource($result['conversation']),
                'user_message' => new MessageResource($result['user_message']),
                'assistant_message' => new MessageResource($result['assistant_message']),
            ],
        ]);
    }
}
