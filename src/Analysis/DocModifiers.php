<?php

declare(strict_types=1);

namespace LaravelReady\Analysis;

use Illuminate\Support\Collection;
use LaravelReady\Analysis\Enums\AllowKeyword;
use LaravelReady\Analysis\Enums\BlockedFunction;
use LaravelReady\Analysis\Enums\SuperglobalName;

final readonly class DocModifiers
{
    /**
     * @param  Collection<array-key, SuperglobalName|BlockedFunction|AllowKeyword>|null  $allows
     */
    public function __construct(
        public bool $skipCheck = false,
        public ?Collection $allows = null,
    ) {}
}
