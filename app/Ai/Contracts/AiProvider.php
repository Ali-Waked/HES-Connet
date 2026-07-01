<?php

declare(strict_types=1);

namespace App\Ai\Contracts;

interface AiProvider
{
    public function chat(string $systemPrompt, string $userMessage, array $tools = []): string;

    public function chatWithTools(string $systemPrompt, string $userMessage, array $tools): array;
}
