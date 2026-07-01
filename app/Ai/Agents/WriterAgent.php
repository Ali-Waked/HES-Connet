<?php

declare(strict_types=1);

namespace App\Ai\Agents;

use App\Ai\Contracts\Agent;

class WriterAgent implements Agent
{
    public function instructions(): string
    {
        return 'You are an expert content writer. Write complete, well-structured blog posts in a clear, engaging style. Format your response with proper Markdown.';
    }

    public function tools(): array
    {
        return [];
    }
}
