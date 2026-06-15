<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactMessage\UpdateContactMessageStatusRequest;
use App\Http\Resources\ContactMessageResource;
use App\Models\ContactMessage;
use App\Services\ContactMessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ContactMessageController extends Controller
{
    public function __construct(
        private readonly ContactMessageService $contactMessageService
    ) {}

    public function index(): AnonymousResourceCollection
    {
        return ContactMessageResource::collection(
            $this->contactMessageService->paginate(
                (int) request('per_page', 15),
                request('search'),
                request('status')
            )
        );
    }

    public function show(ContactMessage $contactMessage): JsonResponse
    {
        $contactMessage = $this->contactMessageService->markAsRead($contactMessage);

        return response()->json([
            'data' => new ContactMessageResource($contactMessage),
        ]);
    }

    public function updateStatus(
        UpdateContactMessageStatusRequest $request,
        ContactMessage $contactMessage
    ): JsonResponse {
        $contactMessage = $this->contactMessageService->updateStatus(
            $contactMessage,
            $request->validated()['status']
        );

        return response()->json([
            'data' => new ContactMessageResource($contactMessage),
        ]);
    }
}
