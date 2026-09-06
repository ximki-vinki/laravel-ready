<?php

declare(strict_types=1);

namespace LaravelReady\Console\Output;

use Illuminate\Support\Collection;
use LaravelReady\Analysis\Displayable;

final readonly class FindingSection
{
    /**
     * @param  Collection<array-key, Displayable>  $findings
     */
    public function __construct(
        public FindingSectionLabel $label,
        public Collection $findings,
    ) {}
}
