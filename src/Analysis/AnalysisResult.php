<?php

declare(strict_types=1);

namespace LaravelReady\Analysis;

use Illuminate\Support\Collection;
use LaravelReady\Analysis\Findings\Finding;

final readonly class AnalysisResult
{
    /**
     * @param  Collection<array-key, Finding>  $findings
     */
    public function __construct(
        public Collection $findings,
        public bool $skipCheck = false,
    ) {}
}
