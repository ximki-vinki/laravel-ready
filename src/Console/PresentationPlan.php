<?php

declare(strict_types=1);

namespace LaravelReady\Console;

use LaravelReady\Console\Output\ReadinessFooter;

final readonly class PresentationPlan
{
    public function __construct(
        public HeaderStyle $headerStyle,
        public bool $showFindings,
        public ?ReadinessFooter $footer,
        public int $exitCode,
    ) {}
}
