<?php

declare(strict_types=1);

namespace LaravelReady\Analysis;

use Illuminate\Support\Collection;

final readonly class ReadinessResult
{
    /**
     * @param  Collection<array-key, Finding>  $findings
     */
    public function __construct(
        public ReadinessLevel $level,
        public Collection $findings,
    ) {}
}
