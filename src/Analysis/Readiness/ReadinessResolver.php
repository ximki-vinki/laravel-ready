<?php

declare(strict_types=1);

namespace LaravelReady\Analysis\Readiness;

use Illuminate\Support\Collection;
use LaravelReady\Analysis\AnalysisResult;
use LaravelReady\Analysis\Enums\Tag;
use LaravelReady\Analysis\Findings\TagFinding;
use LaravelReady\Analysis\Readiness\Guard\GuardEvaluator;

final class ReadinessResolver
{
    public function resolve(AnalysisResult $result, string $appRoot): ReadinessResult
    {
        $actual = $this->actual($result);
        $result = new UseDependencyChecker($appRoot)->check($result, $actual);

        return new ReadinessResult(
            actual: $actual,
            hasBlockers: (new GuardEvaluator)->hasBlockers($result, $actual),
            findings: $result->findings,
        );
    }

    /**
     * @param  Collection<array-key, Tag>  $tags
     */
    private function actualFromTags(Collection $tags): ReadinessLevel
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

    private function actual(AnalysisResult $result): ReadinessLevel
    {
        return $this->actualFromTags(TagFinding::uniqueTags($result->findings));
    }
}
