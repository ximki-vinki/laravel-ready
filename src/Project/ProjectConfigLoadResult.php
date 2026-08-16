<?php

declare(strict_types=1);

namespace LaravelReady\Project;

final readonly class ProjectConfigLoadResult
{
    public function __construct(
        public ?ProjectConfig $config,
        public ?string $error,
    ) {}

    public function isSuccess(): bool
    {
        return $this->error === null;
    }
}
