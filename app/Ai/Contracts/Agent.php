<?php

declare(strict_types=1);

namespace App\Ai\Contracts;

interface Agent
{
    public function instructions(): string;

    public function tools(): array;
}
