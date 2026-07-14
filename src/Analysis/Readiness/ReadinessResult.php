<?php

declare(strict_types=1);

namespace LaravelReady\Analysis\Readiness;

use Illuminate\Support\Collection;
use LaravelReady\Analysis\Findings\Finding;

final readonly class ReadinessResult
{
    /**
     * @param  Collection<array-key, Finding>  $findings
     */
    public function __construct(
        public ReadinessLevel $actual,
        public bool $hasBlockers,
        public Collection $findings,
        public bool $skipCheck = false,
    ) {}
}
