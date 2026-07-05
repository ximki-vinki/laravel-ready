<?php

declare(strict_types=1);

use LaravelReady\Analysis\ReadinessLevel;
use LaravelReady\Analysis\ReadinessResult;
use LaravelReady\Console\ReportScenario;
use LaravelReady\Console\ReportScenarioResolver;

covers(ReportScenarioResolver::class);

it('resolves clean for laravel ready without blockers', function () {
    $readiness = new ReadinessResult(ReadinessLevel::LaravelReady, false, collect());

    expect((new ReportScenarioResolver)->resolve($readiness))->toBe(ReportScenario::Clean);
});

it('resolves clean for laravel adapter without blockers', function () {
    $readiness = new ReadinessResult(ReadinessLevel::LaravelAdapter, false, collect());

    expect((new ReportScenarioResolver)->resolve($readiness))->toBe(ReportScenario::Clean);
});

it('resolves guard failed when laravel ready has blockers', function () {
    $readiness = new ReadinessResult(ReadinessLevel::LaravelReady, true, collect());

    expect((new ReportScenarioResolver)->resolve($readiness))->toBe(ReportScenario::GuardFailed);
});

it('resolves tag invalid for untagged', function () {
    $readiness = new ReadinessResult(ReadinessLevel::Untagged, true, collect());

    expect((new ReportScenarioResolver)->resolve($readiness))->toBe(ReportScenario::TagInvalid);
});
