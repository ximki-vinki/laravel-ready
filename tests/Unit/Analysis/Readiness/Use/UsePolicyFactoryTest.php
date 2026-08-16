<?php

declare(strict_types=1);

use LaravelReady\Analysis\Readiness\ReadinessLevel;
use LaravelReady\Analysis\Readiness\Use\LaravelAdapterUsePolicy;
use LaravelReady\Analysis\Readiness\Use\LaravelReadyUsePolicy;
use LaravelReady\Analysis\Readiness\Use\LegacyAdapterUsePolicy;
use LaravelReady\Analysis\Readiness\Use\LegacyPerfectUsePolicy;
use LaravelReady\Analysis\Readiness\Use\UsePolicyFactory;

covers(UsePolicyFactory::class);

it('creates the policy for a guarded readiness level', function (
    ReadinessLevel $level,
    string $expectedPolicy,
): void {
    expect(appUsePolicyFactory()->create($level))->toBeInstanceOf($expectedPolicy);
})->with([
    [ReadinessLevel::LaravelReady, LaravelReadyUsePolicy::class],
    [ReadinessLevel::LaravelAdapter, LaravelAdapterUsePolicy::class],
    [ReadinessLevel::LegacyAdapter, LegacyAdapterUsePolicy::class],
    [ReadinessLevel::LegacyPerfect, LegacyPerfectUsePolicy::class],
]);

it('does not create a policy for an unguarded readiness level', function (): void {
    expect(appUsePolicyFactory()->create(ReadinessLevel::Legacy))->toBeNull();
});
