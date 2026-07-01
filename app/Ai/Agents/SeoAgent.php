<?php

declare(strict_types=1);

namespace App\Ai\Agents;

use App\Ai\Contracts\Agent;

class SeoAgent implements Agent
{
    public function instructions(): string
    {
        return 'You are an expert SEO content writer. Analyze the provided blog post and generate SEO metadata including a title, meta description, keywords, and a summary. Respond with valid JSON only in this format: {"title": "...", "description": "...", "keywords": ["...", "..."], "summary": "..."}';
    }

    public function tools(): array
    {
        return [];
    }
}
