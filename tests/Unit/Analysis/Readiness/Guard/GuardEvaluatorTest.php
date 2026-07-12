<?php

declare(strict_types=1);

use LaravelReady\Analysis\AnalysisResult;
use LaravelReady\Analysis\Enums\SuperglobalName;
use LaravelReady\Analysis\Findings\SuperglobalFinding;
use LaravelReady\Analysis\Findings\UseFinding;
use LaravelReady\Analysis\Readiness\Guard\GuardEvaluator;
use LaravelReady\Analysis\Readiness\ReadinessLevel;

covers(GuardEvaluator::class);

it('treats untagged as blockers', function (): void {
    $result = new AnalysisResult(collect());
    $guard = (new GuardEvaluator)->hasBlockers($result, ReadinessLevel::Untagged);

    expect($guard)->toBeTrue();
});

it('treats multitag as blockers', function (): void {
    $result = new AnalysisResult(collect());
    $guard = (new GuardEvaluator)->hasBlockers($result, ReadinessLevel::MultiTag);

    expect($guard)->toBeTrue();
});

it('does not block legacy level', function (): void {
    $result = new AnalysisResult(collect([
        new SuperglobalFinding(SuperglobalName::Get, 5),
    ]));
    $guard = (new GuardEvaluator)->hasBlockers($result, ReadinessLevel::Legacy);

    expect($guard)->toBeFalse();
});

it('does not block legacy-adapter level', function (): void {
    $result = new AnalysisResult(collect([
        new SuperglobalFinding(SuperglobalName::Get, 5),
    ]));
    $guard = (new GuardEvaluator)->hasBlockers($result, ReadinessLevel::LegacyAdapter);

    expect($guard)->toBeFalse();
});

it('blocks legacy-perfect on ast finding', function (): void {
    $result = new AnalysisResult(collect([
        new SuperglobalFinding(SuperglobalName::Get, 5),
    ]));
    $guard = (new GuardEvaluator)->hasBlockers($result, ReadinessLevel::LegacyPerfect);

    expect($guard)->toBeTrue();
});

it('blocks legacy-perfect on use finding', function (): void {
    $result = new AnalysisResult(collect([
        new UseFinding('App\Domain\TaggedService', 5),
    ]));
    $guard = (new GuardEvaluator)->hasBlockers($result, ReadinessLevel::LegacyPerfect);

    expect($guard)->toBeTrue();
});

it('blocks legacy-adapter on use finding', function (): void {
    $result = new AnalysisResult(collect([
        new UseFinding('App\Domain\TaggedService', 5),
    ]));
    $guard = (new GuardEvaluator)->hasBlockers($result, ReadinessLevel::LegacyAdapter);

    expect($guard)->toBeTrue();
});

it('blocks laravel-ready on legacy finding', function (): void {
    $result = new AnalysisResult(collect([
        new SuperglobalFinding(SuperglobalName::Get, 5),
    ]));
    $guard = (new GuardEvaluator)->hasBlockers($result, ReadinessLevel::LaravelReady);

    expect($guard)->toBeTrue();
});

it('blocks laravel-ready on use finding', function (): void {
    $result = new AnalysisResult(collect([
        new UseFinding('Wf\Legacy\OldRepo', 5),
    ]));
    $guard = (new GuardEvaluator)->hasBlockers($result, ReadinessLevel::LaravelReady);

    expect($guard)->toBeTrue();
});

it('does not block laravel-ready without findings', function (): void {
    $result = new AnalysisResult(collect());
    $guard = (new GuardEvaluator)->hasBlockers($result, ReadinessLevel::LaravelReady);

    expect($guard)->toBeFalse();
});

it('blocks laravel-adapter on ast finding', function (): void {
    $result = new AnalysisResult(collect([
        new SuperglobalFinding(SuperglobalName::Get, 5),
    ]));
    $guard = (new GuardEvaluator)->hasBlockers($result, ReadinessLevel::LaravelAdapter);

    expect($guard)->toBeTrue();
});

it('does not block laravel-adapter on use finding only', function (): void {
    $result = new AnalysisResult(collect([
        new UseFinding('App\Domain\UntaggedService', 5),
    ]));
    $guard = (new GuardEvaluator)->hasBlockers($result, ReadinessLevel::LaravelAdapter);

    expect($guard)->toBeFalse();
});
