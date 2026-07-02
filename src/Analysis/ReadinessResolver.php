<?php

declare(strict_types=1);

namespace LaravelReady\Analysis;

use Illuminate\Support\Collection;

final class ReadinessResolver
{
    public function resolve(AnalysisResult $result): ReadinessResult
    {
        $actual = $this->actual($result);
        $pledged = $this->pledged($result);

        return new ReadinessResult(
            actual: $actual,
            pledged: $pledged,
            pledgeViolated: $this->pledgeViolated($result, $pledged),
            findings: $result->findings,
        );
    }

    /**
     * @param  Collection<array-key, Tag>  $tags
     */
    private function actualFromTags(Collection $tags): ReadinessLevel
    {
        return match (true) {
            $tags->isEmpty(), $tags->count() > 1 => ReadinessLevel::Untagged,
            $tags->first() === Tag::LaravelReady => ReadinessLevel::LaravelReady,
            $tags->first() === Tag::Legacy => ReadinessLevel::Legacy,
        };
    }

    private function actual(AnalysisResult $result): ReadinessLevel
    {
        return $this->actualFromTags($this->uniqueTags($result));
    }

    private function pledgeViolated(AnalysisResult $result, ?ReadinessLevel $pledged): ?bool
    {
        if ($pledged === null) {
            return null;
        }

        return $result->findings->contains(
            fn (Finding $finding): bool => $finding instanceof LegacyFinding,
        );
    }

    private function pledged(AnalysisResult $result): ?ReadinessLevel
    {
        $hasLaravelReadyTag = $this->uniqueTags($result)->contains(Tag::LaravelReady);

        return $hasLaravelReadyTag ? ReadinessLevel::LaravelReady : null;
    }

    /**
     * @return Collection<array-key, Tag>
     */
    private function uniqueTags(AnalysisResult $result): Collection
    {
        return $result->findings
            ->filter(fn (Finding $finding): bool => $finding instanceof TagFinding)
            ->map(fn (TagFinding $finding): Tag => $finding->tag)
            ->unique()
            ->values();
    }
}
