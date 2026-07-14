<?php

declare(strict_types=1);

namespace LaravelReady\Analysis;

use Illuminate\Support\Collection;
use LaravelReady\Analysis\Enums\AllowKeyword;
use LaravelReady\Analysis\Enums\BlockedFunction;
use LaravelReady\Analysis\Enums\SuperglobalName;
use LaravelReady\Analysis\Findings\Finding;

final readonly class AnalysisResult
{
    /**
     * @param  Collection<array-key, Finding>  $findings
     * @param  Collection<array-key, SuperglobalName|BlockedFunction|AllowKeyword>|null  $allows
     */
    public function __construct(
        public Collection $findings,
        public bool $skipCheck = false,
        public ?Collection $allows = null,
    ) {}
}
