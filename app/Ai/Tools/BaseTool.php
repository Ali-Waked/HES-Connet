<?php

declare(strict_types=1);

namespace App\Ai\Tools;

abstract class BaseTool
{
    abstract public function name(): string;

    abstract public function description(): string;

    abstract public function parameters(): array;

    abstract public function handle(array $arguments): mixed;

    public function toArray(): array
    {
        return [
            'name' => $this->name(),
            'description' => $this->description(),
            'parameters' => $this->parameters(),
        ];
    }

    public function __invoke(array $arguments): mixed
    {
        return $this->handle($arguments);
    }
}
