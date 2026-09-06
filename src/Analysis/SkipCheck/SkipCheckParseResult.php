<?php

declare(strict_types=1);

namespace LaravelReady\Analysis\SkipCheck;

final readonly class SkipCheckParseResult
{
    public function __construct(public ?string $date) {}
}
