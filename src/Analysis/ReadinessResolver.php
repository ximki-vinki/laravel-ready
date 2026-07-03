<?php

declare(strict_types=1);

namespace LaravelReady\Analysis;

use Illuminate\Support\Collection;

final class ReadinessResolver
{
    public function resolve(AnalysisResult $result): ReadinessResult
    {
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
        return match (true) {
            $tags->isEmpty() => ReadinessLevel::Untagged,
            $tags->count() > 1 => ReadinessLevel::MultiTag,
            default => match ($tags->first()) {
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
        if (in_array($actual, [ReadinessLevel::MultiTag, ReadinessLevel::Untagged], true)) {
            return true;
        }

        // TODO пока работаем только с LaravelReady, что бы можно уже было пользоваться
        if ($actual !== ReadinessLevel::LaravelReady) {
            return false;
        }
        if ($this->hasDeniedWfImport($result)) {
            return true;
        }

        return $result->findings->contains(
            fn (Finding $finding): bool => $finding instanceof LegacyFinding,
        );
    }

    private function hasDeniedWfImport(AnalysisResult $result): bool
    {
        return $result->findings->contains(
            fn (Finding $finding): bool => $finding instanceof UseImportFinding
                && str_starts_with($finding->fqcn, 'Wf\\'),
        );
    }
}
