<?php

declare(strict_types=1);

namespace LaravelReady\Console\Output;

use Illuminate\Support\Collection;
use LaravelReady\Analysis\Finding;
use LaravelReady\Analysis\Tag;
use LaravelReady\Analysis\TagFinding;

enum TagStatus: string
{
    case Untagged = 'untagged';
    case Multi = 'multi';
    case LaravelReady = '@laravel-ready';
    case Legacy = '@legacy-code';

    /**
     * @param  Collection<array-key, Finding>  $findings
     */
    public static function fromFindings(Collection $findings): self
    {
        $tags = $findings
            ->filter(fn (Finding $finding): bool => $finding instanceof TagFinding)
            ->map(fn (TagFinding $finding): Tag => $finding->tag)
            ->unique()
            ->values();

        return match (true) {
            $tags->isEmpty() => self::Untagged,
            $tags->count() > 1 => self::Multi,
            default => match ($tags->first()) {
                Tag::LaravelReady => self::LaravelReady,
                Tag::Legacy => self::Legacy,
            },
        };
    }

    public function display(): string
    {
        return $this->value;
    }
}
