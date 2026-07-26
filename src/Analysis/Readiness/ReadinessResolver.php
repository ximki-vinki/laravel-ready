<?php

declare(strict_types=1);

namespace LaravelReady\Analysis\Readiness;

use LaravelReady\Analysis\AnalysisResult;
use LaravelReady\Analysis\Readiness\Guard\GuardEvaluator;
use LaravelReady\Analysis\Resolution\Psr4ClassLocator;

final readonly class ReadinessResolver
{
    public function resolve(AnalysisResult $result, Psr4ClassLocator $locator): ReadinessResult
    {
        $actual = new ReadinessLevelResolver()->fromResult($result);
        $result = new UseDependencyChecker($locator)->check($result, $actual);

        return new ReadinessResult(
            actual: $actual,
            hasBlockers: (new GuardEvaluator)->hasBlockers($result, $actual),
            findings: $result->findings,
            skipCheck: $result->skipCheck,
        );
    }
}
