<?php

declare(strict_types=1);

namespace LaravelReady\Analysis;

final class ReadinessResolver
{
    public function resolve(AnalysisResult $result): ReadinessResult
    {
        return new ReadinessResult(
            level: $this->level($result),
            findings: $result->findings,
        );
    }

    private function level(AnalysisResult $result): ReadinessLevel
    {
        if ($result->findings->contains(
            fn (Finding $finding): bool => $finding instanceof LegacyFinding,
        )) {
            return ReadinessLevel::Legacy;
        }

        return ReadinessLevel::LaravelReady;
    }
}
