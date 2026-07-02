<?php

declare(strict_types=1);

namespace LaravelReady\Analysis;

final class ReadinessResolver
{
    public function resolve(AnalysisResult $result): ReadinessResult
    {
        $actual = $this->actual($result);
        $pledged = $this->pledged($result);

        return new ReadinessResult(
            actual: $actual,
            pledged: $pledged,
            pledgeViolated: $this->pledgeViolated($actual, $pledged),
            findings: $result->findings,
        );
    }

    private function pledgeViolated(ReadinessLevel $actual, ?ReadinessLevel $pledged): ?bool
    {
        if ($pledged === null) {
            return null;
        }

        return $pledged === ReadinessLevel::LaravelReady
            && $actual === ReadinessLevel::Legacy;
    }

    private function pledged(AnalysisResult $result): ?ReadinessLevel
    {
        return match ($result->tag) {
            Tag::LaravelReady => ReadinessLevel::LaravelReady,
            default => null,
        };
    }

    private function actual(AnalysisResult $result): ReadinessLevel
    {
        if ($result->findings->contains(
            fn (Finding $finding): bool => $finding instanceof LegacyFinding,
        )) {
            return ReadinessLevel::Legacy;
        }

        return ReadinessLevel::LaravelReady;
    }
}
