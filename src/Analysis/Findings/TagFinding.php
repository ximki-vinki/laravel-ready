<?php

declare(strict_types=1);

namespace LaravelReady\Analysis\Findings;

use Illuminate\Support\Collection;
use LaravelReady\Analysis\Enums\Tag;

final readonly class TagFinding implements Finding
{
    public function __construct(
        public Tag $tag,
        public int $line,
    ) {}

    /**
     * @param  Collection<array-key, Finding>  $findings
     * @return Collection<array-key, Tag>
     */
    public static function uniqueTags(Collection $findings): Collection
    {
        return $findings
            ->flatMap(fn (Finding $finding): array => $finding instanceof self ? [$finding->tag] : [])
            ->unique()
            ->values();
    }

    public function display(): string
    {
        return 'tag: @'.$this->tag->value.' (line '.$this->line.')';
    }
}
