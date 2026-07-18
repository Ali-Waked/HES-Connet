<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Patient;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ai\RecommendDoctorRequest;
use App\Http\Requests\Conversation\StoreConversationRequest;
use App\Http\Requests\Conversation\StoreMessageRequest;
use App\Http\Resources\ConversationResource;
use App\Http\Resources\DoctorRecommendationResource;
use App\Http\Resources\MessageResource;
use App\Models\AiMedicalConversation;
use App\Services\AiConversationService;
use App\Services\MedicalTriage\DoctorRecommendationService;
use App\Services\MedicalTriage\MedicalTriageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiConversationController extends Controller
{
    public function __construct(
        private readonly AiConversationService $conversationService,
        private readonly MedicalTriageService $triageService,
        private readonly DoctorRecommendationService $doctorRecommendationService,
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

    public function recommendDoctor(RecommendDoctorRequest $request, string $uuid): JsonResponse
    {
        $conversation = $this->conversationService->getConversation($uuid, $request->user()->id);

        if ($conversation->message_count < 4) {
            return response()->json([
                'message' => 'Please continue the conversation to gather more symptoms before requesting doctor recommendations.',
            ], 422);
        }

        if (! $conversation->estimated_specialty && ! $conversation->triage_status === 'ready') {
            $triageResult = $this->triageService->analyze($conversation);

            $conversation->updateTriageData([
                'symptoms' => $triageResult->symptoms,
                'specialty' => $triageResult->specialty,
                'urgency' => $triageResult->urgency,
                'confidence' => $triageResult->confidence,
                'ready_for_recommendation' => true,
            ]);
        }

        $specialty = $conversation->estimated_specialty ?? 'General Practice';

        $doctors = $this->doctorRecommendationService->findDoctors($specialty);

        $conversation->markAsRecommended();

        if (empty($doctors)) {
            return response()->json([
                'data' => [
                    'triage' => [
                        'specialty' => $specialty,
                        'urgency' => $conversation->urgency,
                        'confidence' => $conversation->confidence,
                        'symptoms' => $conversation->extracted_symptoms ?? [],
                    ],
                    'doctors' => [],
                    'message' => 'No suitable doctor is currently available for this specialty.',
                ],
            ]);
        }

        return response()->json([
            'data' => [
                'triage' => [
                    'specialty' => $specialty,
                    'urgency' => $conversation->urgency,
                    'confidence' => $conversation->confidence,
                    'symptoms' => $conversation->extracted_symptoms ?? [],
                ],
                'doctors' => DoctorRecommendationResource::collection($doctors),
            ],
        ]);
    }
}
