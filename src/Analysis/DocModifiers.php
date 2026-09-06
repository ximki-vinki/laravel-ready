<?php

declare(strict_types=1);

namespace LaravelReady\Analysis;

use LaravelReady\Analysis\Allows\AllowsParseResult;

final readonly class DocModifiers
{
    public function __construct(
        public bool $skipCheck = false,
        public ?AllowsParseResult $allows = null,
    ) {}
}
