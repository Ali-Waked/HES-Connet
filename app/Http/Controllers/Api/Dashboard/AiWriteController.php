<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Dashboard;

use App\Ai\Agents\WriterAgent;
use App\Ai\Contracts\AiProvider;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiWriteController extends Controller
{
    public function __construct(
        private readonly AiProvider $provider,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $prompt = "Write a complete blog post about: {$request->message}";

        $agent = new WriterAgent;
        $content = $this->provider->chat($agent->instructions(), $prompt);

        return response()->json([
            'content' => $content,
        ]);
    }
}
