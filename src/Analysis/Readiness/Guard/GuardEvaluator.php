<?php

declare(strict_types=1);

namespace LaravelReady\Analysis\Readiness\Guard;

use LaravelReady\Analysis\AnalysisResult;
use LaravelReady\Analysis\Findings\Finding;
use LaravelReady\Analysis\Findings\LegacyFinding;
use LaravelReady\Analysis\Findings\UseFinding;
use LaravelReady\Analysis\Readiness\ReadinessLevel;

final class GuardEvaluator
{
    public function hasBlockers(AnalysisResult $result, ReadinessLevel $actual): bool
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
