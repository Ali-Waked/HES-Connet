<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Dashboard\Ai\AskRequest;
use App\Http\Requests\Api\Dashboard\Ai\RenameConversationRequest;
use App\Http\Resources\Dashboard\Ai\ConversationListResource;
use App\Http\Resources\Dashboard\Ai\ConversationResource;
use App\Http\Resources\Dashboard\Ai\MessageResource;
use App\Services\Dashboard\Ai\AiChatService;
use App\Services\Dashboard\Ai\ConversationService;
use App\Services\Dashboard\Ai\MessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiController extends Controller
{
    public function __construct(
        private readonly AiChatService $aiChatService,
        private readonly ConversationService $conversationService,
        private readonly MessageService $messageService,
    ) {}

    public function ask(AskRequest $request): JsonResponse
    {
        $result = $this->aiChatService->ask(
            userId: $request->user()->id,
            conversationUuid: $request->validated('conversation_uuid'),
            message: $request->validated('message'),
        );

        return response()->json([
            'conversation' => [
                'uuid' => $result['conversation']->uuid,
                'title' => $result['conversation']->title,
                'language' => $result['conversation']->language,
            ],
            'assistant' => [
                'message' => $result['message'],
                'tools_used' => $result['tools_used'],
            ],
        ]);
    }

    public function conversations(Request $request): JsonResponse
    {
        if (! $request->user()->hasSystemRole('super_admin')) {
            abort(403, 'Only super admins can access AI conversations.');
        }

        $conversations = $this->conversationService->list(
            userId: $request->user()->id,
        );

        return response()->json([
            'data' => ConversationListResource::collection($conversations),
            'meta' => [
                'current_page' => $conversations->currentPage(),
                'last_page' => $conversations->lastPage(),
                'total' => $conversations->total(),
            ],
        ]);
    }

    public function show(Request $request, string $uuid): JsonResponse
    {
        if (! $request->user()->hasSystemRole('super_admin')) {
            abort(403);
        }

        $conversation = $this->conversationService->getByUuid($uuid, $request->user()->id);

        $messages = $conversation->messages()
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'data' => [
                'conversation' => new ConversationResource($conversation),
                'messages' => MessageResource::collection($messages),
            ],
        ]);
    }

    public function destroy(Request $request, string $uuid): JsonResponse
    {
        if (! $request->user()->hasSystemRole('super_admin')) {
            abort(403);
        }

        $conversation = $this->conversationService->getByUuid($uuid, $request->user()->id);

        $this->conversationService->delete($conversation);

        return response()->json(['message' => 'Conversation deleted.']);
    }

    public function rename(RenameConversationRequest $request, string $uuid): JsonResponse
    {
        $conversation = $this->conversationService->getByUuid($uuid, $request->user()->id);

        $conversation = $this->conversationService->rename(
            $conversation,
            $request->validated('title'),
        );

        return response()->json([
            'data' => new ConversationResource($conversation),
        ]);
    }
}
