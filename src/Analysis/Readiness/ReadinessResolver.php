<?php

declare(strict_types=1);

namespace LaravelReady\Analysis\Readiness;

use Illuminate\Support\Collection;
use LaravelReady\Analysis\AnalysisResult;
use LaravelReady\Analysis\Enums\Tag;
use LaravelReady\Analysis\Findings\Finding;
use LaravelReady\Analysis\Findings\LegacyFinding;
use LaravelReady\Analysis\Findings\TagFinding;
use LaravelReady\Analysis\Findings\UseFinding;

final class ReadinessResolver
{
    public function resolve(AnalysisResult $result, string $projectRoot): ReadinessResult
    {
        $result = new UseDependencyChecker($projectRoot)->check($result);
        $actual = $this->actual($result);

        return new ReadinessResult(
            actual: $actual,
            hasBlockers: $this->hasBlockers($result, $actual),
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

    private function hasBlockers(AnalysisResult $result, ReadinessLevel $actual): bool
    {
        if (in_array($actual, [ReadinessLevel::MultiTag, ReadinessLevel::Untagged])) {
            return true;
        }

        if ($actual === ReadinessLevel::LaravelAdapter) {
            return $result->findings->contains(
                fn (Finding $finding): bool => $finding instanceof LegacyFinding && ! $finding instanceof UseFinding,
            );
        }

        // TODO пока работаем только с LaravelReady, что бы можно уже было пользоваться
        if ($actual !== ReadinessLevel::LaravelReady) {
            return false;
        }

        return $result->findings->contains(
            fn (Finding $finding): bool => $finding instanceof LegacyFinding,
        );
    }
}
