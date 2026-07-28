<?php

declare(strict_types=1);

namespace LaravelReady\Analysis\Readiness;

use LaravelReady\Analysis\AnalysisResult;
use LaravelReady\Analysis\Readiness\Guard\GuardEvaluator;

final readonly class ReadinessResolver
{
    public function __construct(private UseDependencyChecker $useDependencyChecker) {}

    public function resolve(AnalysisResult $result): ReadinessResult
    {
        $actual = new ReadinessLevelResolver()->fromResult($result);
        $result = $this->useDependencyChecker->check($result, $actual);

        return new ReadinessResult(
            actual: $actual,
            hasBlockers: (new GuardEvaluator)->hasBlockers($result, $actual),
            findings: $result->findings,
            skipCheck: $result->skipCheck,
        );
    }
}
