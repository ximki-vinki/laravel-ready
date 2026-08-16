<?php

declare(strict_types=1);

namespace LaravelReady\Analysis\Readiness;

use LaravelReady\Analysis\AnalysisResult;
use LaravelReady\Analysis\Readiness\Use\UsePolicy;
use LaravelReady\Analysis\Readiness\Use\UsePolicyFactory;

final readonly class UseDependencyChecker
{
    public function __construct(
        private UsePolicyFactory $policyFactory,
    ) {}

    public function check(AnalysisResult $result, ReadinessLevel $actual): AnalysisResult
    {
        $policy = $this->policyFactory->create($actual);

        if (! $policy instanceof UsePolicy) {
            return $result;
        }

        $violations = $policy->violations($result);

        if ($violations->isEmpty()) {
            return $result;
        }

        return new AnalysisResult(
            findings: $result->findings->merge($violations),
            skipCheck: $result->skipCheck,
            allows: $result->allows,
        );
    }
}
