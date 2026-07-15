<?php

declare(strict_types=1);

namespace LaravelReady\Analysis\Allows;

use Illuminate\Support\Collection;
use LaravelReady\Analysis\Enums\AllowKeyword;
use LaravelReady\Analysis\Enums\BlockedFunction;
use LaravelReady\Analysis\Enums\SuperglobalName;
use LaravelReady\Analysis\Findings\UnknownAllowTokenFinding;

final readonly class AllowsParseResult
{
    /**
     * @param  Collection<array-key, SuperglobalName|BlockedFunction|AllowKeyword>  $tokens
     * @param  Collection<array-key, UnknownAllowTokenFinding>  $unknowns
     */
    public function __construct(
        public Collection $tokens,
        public Collection $unknowns,
    ) {}

    public static function empty(): self
    {
        return new self(collect(), collect());
    }

    public function withToken(SuperglobalName|BlockedFunction|AllowKeyword $token): self
    {
        return new self($this->tokens->concat([$token]), $this->unknowns);
    }

    public function withUnknown(UnknownAllowTokenFinding $finding): self
    {
        return new self($this->tokens, $this->unknowns->concat([$finding]));
    }
}
