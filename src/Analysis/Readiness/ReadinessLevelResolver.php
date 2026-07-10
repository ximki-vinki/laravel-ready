<?php

declare(strict_types=1);

namespace LaravelReady\Analysis\Readiness;

use Illuminate\Support\Collection;
use LaravelReady\Analysis\AnalysisResult;
use LaravelReady\Analysis\Enums\Tag;
use LaravelReady\Analysis\Findings\TagFinding;

final class ReadinessLevelResolver
{
    public function fromResult(AnalysisResult $result): ReadinessLevel
    {
        return $this->fromTags(TagFinding::uniqueTags($result->findings));
    }

    /**
     * @param  Collection<array-key, Tag>  $tags
     */
    private function fromTags(Collection $tags): ReadinessLevel
    {
        return match (true) { // @pest-mutate-ignore: TrueToFalse
            $tags->isEmpty() => ReadinessLevel::Untagged,
            $tags->count() > 1 => ReadinessLevel::MultiTag,
            default => match ($tags->first()) {
                Tag::LaravelAdapter => ReadinessLevel::LaravelAdapter,
                Tag::LaravelReady => ReadinessLevel::LaravelReady,
                Tag::Legacy => ReadinessLevel::Legacy,
            },
        };
    }
}
