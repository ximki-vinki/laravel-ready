<?php

declare(strict_types=1);

use LaravelReady\Analysis\ReadinessLevel;
use LaravelReady\Analysis\ReadinessResult;
use LaravelReady\Console\HeaderStyle;
use LaravelReady\Console\Output\ReadinessFooter;
use LaravelReady\Console\PresentationPlan;
use LaravelReady\Console\PresentationPlanBuilder;

covers(PresentationPlanBuilder::class);

it('builds clean plan for laravel ready without blockers', function () {
    $readiness = new ReadinessResult(ReadinessLevel::LaravelReady, false, collect());
    $plan = (new PresentationPlanBuilder)->build($readiness);

    expect($plan)->toEqual(new PresentationPlan(
        headerStyle: HeaderStyle::Clean,
        showFindings: false,
        footer: null,
        exitCode: 0,
    ));
});

it('builds clean plan for laravel adapter without blockers', function () {
    $readiness = new ReadinessResult(ReadinessLevel::LaravelAdapter, false, collect());
    $plan = (new PresentationPlanBuilder)->build($readiness);

    expect($plan->showFindings)->toBeFalse()
        ->and($plan->footer)->toBeNull()
        ->and($plan->exitCode)->toBe(0);
});

it('builds legacy info plan with findings and success exit', function () {
    $readiness = new ReadinessResult(ReadinessLevel::Legacy, false, collect());
    $plan = (new PresentationPlanBuilder)->build($readiness);

    expect($plan->headerStyle)->toBe(HeaderStyle::Warning)
        ->and($plan->showFindings)->toBeTrue()
        ->and($plan->footer)->toBeNull()
        ->and($plan->exitCode)->toBe(0);
});

it('builds tag invalid plan for untagged', function () {
    $readiness = new ReadinessResult(ReadinessLevel::Untagged, true, collect());
    $plan = (new PresentationPlanBuilder)->build($readiness);

    expect($plan->headerStyle)->toBe(HeaderStyle::Clean)
        ->and($plan->showFindings)->toBeTrue()
        ->and($plan->footer)->toBe(ReadinessFooter::NotGuarded)
        ->and($plan->exitCode)->toBe(1);
});

it('builds tag invalid plan for multi tag', function () {
    $readiness = new ReadinessResult(ReadinessLevel::MultiTag, true, collect());
    $plan = (new PresentationPlanBuilder)->build($readiness);

    expect($plan->headerStyle)->toBe(HeaderStyle::Clean)
        ->and($plan->showFindings)->toBeTrue()
        ->and($plan->footer)->toBe(ReadinessFooter::MultiTagFailed)
        ->and($plan->exitCode)->toBe(1);
});

it('builds guard failed plan when laravel ready has blockers', function () {
    $readiness = new ReadinessResult(ReadinessLevel::LaravelReady, true, collect());
    $plan = (new PresentationPlanBuilder)->build($readiness);

    expect($plan->headerStyle)->toBe(HeaderStyle::Error)
        ->and($plan->showFindings)->toBeTrue()
        ->and($plan->footer)->toBe(ReadinessFooter::GuardFailed)
        ->and($plan->exitCode)->toBe(1);
});

it('builds adapter failed plan when laravel adapter has blockers', function () {
    $readiness = new ReadinessResult(ReadinessLevel::LaravelAdapter, true, collect());
    $plan = (new PresentationPlanBuilder)->build($readiness);

    expect($plan->headerStyle)->toBe(HeaderStyle::Error)
        ->and($plan->showFindings)->toBeTrue()
        ->and($plan->footer)->toBe(ReadinessFooter::AdapterFailed)
        ->and($plan->exitCode)->toBe(1);
});
