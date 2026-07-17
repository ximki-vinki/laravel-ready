<?php

declare(strict_types=1);

namespace LaravelReady\Project;

final readonly class NamespaceResolver
{
    public function __construct(
        public string $prefix,
        public string $path,
    ) {}
}
