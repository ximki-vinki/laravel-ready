<?php

declare(strict_types=1);

namespace LaravelReady\Analysis;

use LaravelReady\Analysis\Allows\AllowsParseResult;
use LaravelReady\Analysis\SkipCheck\SkipCheckParseResult;

final readonly class DocModifiers
{
    public function __construct(
        public ?SkipCheckParseResult $skipCheck = null,
        public ?AllowsParseResult $allows = null,
    ) {}
}
