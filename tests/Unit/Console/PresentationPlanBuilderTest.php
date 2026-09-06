<?php

declare(strict_types=1);

use Illuminate\Support\Carbon;
use LaravelReady\Analysis\Readiness\ReadinessLevel;
use LaravelReady\Analysis\Readiness\ReadinessResult;
use LaravelReady\Analysis\SkipCheck\SkipCheckEvaluator;
use LaravelReady\Analysis\SkipCheck\SkipCheckParseResult;
use LaravelReady\Console\HeaderStyle;
use LaravelReady\Console\Output\ReadinessFooter;
use LaravelReady\Console\PresentationPlan;
use LaravelReady\Console\PresentationPlanBuilder;

covers(PresentationPlanBuilder::class);

function planBuilder(): PresentationPlanBuilder
{
    return new PresentationPlanBuilder(new SkipCheckEvaluator(Carbon::parse('2026-03-01')));
}

it('builds clean plan for laravel ready without blockers', function (): void {
    $readiness = new ReadinessResult(ReadinessLevel::LaravelReady, false, collect());
    $plan = planBuilder()->build($readiness);

    expect($plan)->toEqual(new PresentationPlan(
        headerStyle: HeaderStyle::Clean,
        showFindings: false,
        footer: null,
        exitCode: 0,
    ));
});

it('builds clean plan for laravel adapter without blockers', function (): void {
    $readiness = new ReadinessResult(ReadinessLevel::LaravelAdapter, false, collect());
    $plan = planBuilder()->build($readiness);

    expect($plan->showFindings)->toBeFalse()
        ->and($plan->footer)->toBeNull()
        ->and($plan->exitCode)->toBe(0);
});

it('builds legacy info plan with findings and success exit', function (): void {
    $readiness = new ReadinessResult(ReadinessLevel::Legacy, false, collect());
    $plan = planBuilder()->build($readiness);

    expect($plan->headerStyle)->toBe(HeaderStyle::Clean)
        ->and($plan->showFindings)->toBeTrue()
        ->and($plan->footer)->toBeNull()
        ->and($plan->exitCode)->toBe(0);
});

it('builds quiet plan for legacy adapter without blockers', function (): void {
    $readiness = new ReadinessResult(ReadinessLevel::LegacyAdapter, false, collect());
    $plan = planBuilder()->build($readiness);

    expect($plan->headerStyle)->toBe(HeaderStyle::Clean)
        ->and($plan->showFindings)->toBeFalse()
        ->and($plan->footer)->toBeNull()
        ->and($plan->exitCode)->toBe(0);
});

it('builds quiet plan for legacy perfect without blockers', function (): void {
    $readiness = new ReadinessResult(ReadinessLevel::LegacyPerfect, false, collect());
    $plan = planBuilder()->build($readiness);

    expect($plan->headerStyle)->toBe(HeaderStyle::Clean)
        ->and($plan->showFindings)->toBeFalse()
        ->and($plan->footer)->toBeNull()
        ->and($plan->exitCode)->toBe(0);
});

it('builds failed plan when legacy adapter has blockers', function (): void {
    $readiness = new ReadinessResult(ReadinessLevel::LegacyAdapter, true, collect());
    $plan = planBuilder()->build($readiness);

    expect($plan->headerStyle)->toBe(HeaderStyle::Error)
        ->and($plan->showFindings)->toBeTrue()
        ->and($plan->footer)->toBe(ReadinessFooter::LegacyAdapterFailed)
        ->and($plan->exitCode)->toBe(1);
});

it('builds failed plan when legacy perfect has blockers', function (): void {
    $readiness = new ReadinessResult(ReadinessLevel::LegacyPerfect, true, collect());
    $plan = planBuilder()->build($readiness);

    expect($plan->headerStyle)->toBe(HeaderStyle::Error)
        ->and($plan->showFindings)->toBeTrue()
        ->and($plan->footer)->toBe(ReadinessFooter::LegacyPerfectFailed)
        ->and($plan->exitCode)->toBe(1);
});

it('builds tag invalid plan for untagged', function (): void {
    $readiness = new ReadinessResult(ReadinessLevel::Untagged, true, collect());
    $plan = planBuilder()->build($readiness);

    expect($plan->headerStyle)->toBe(HeaderStyle::Clean)
        ->and($plan->showFindings)->toBeTrue()
        ->and($plan->footer)->toBe(ReadinessFooter::NotGuarded)
        ->and($plan->exitCode)->toBe(1);
});

it('builds tag invalid plan for multi tag', function (): void {
    $readiness = new ReadinessResult(ReadinessLevel::MultiTag, true, collect());
    $plan = planBuilder()->build($readiness);

    expect($plan->headerStyle)->toBe(HeaderStyle::Clean)
        ->and($plan->showFindings)->toBeTrue()
        ->and($plan->footer)->toBe(ReadinessFooter::MultiTagFailed)
        ->and($plan->exitCode)->toBe(1);
});

it('builds guard failed plan when laravel ready has blockers', function (): void {
    $readiness = new ReadinessResult(ReadinessLevel::LaravelReady, true, collect());
    $plan = planBuilder()->build($readiness);

    expect($plan->headerStyle)->toBe(HeaderStyle::Error)
        ->and($plan->showFindings)->toBeTrue()
        ->and($plan->footer)->toBe(ReadinessFooter::GuardFailed)
        ->and($plan->exitCode)->toBe(1);
});

it('builds adapter failed plan when laravel adapter has blockers', function (): void {
    $readiness = new ReadinessResult(ReadinessLevel::LaravelAdapter, true, collect());
    $plan = planBuilder()->build($readiness);

    expect($plan->headerStyle)->toBe(HeaderStyle::Error)
        ->and($plan->showFindings)->toBeTrue()
        ->and($plan->footer)->toBe(ReadinessFooter::AdapterFailed)
        ->and($plan->exitCode)->toBe(1);
});

it('does not skip when skipCheck is active but there are no blockers', function (): void {
    $readiness = new ReadinessResult(
        ReadinessLevel::LaravelAdapter,
        false,
        collect(),
        skipCheck: new SkipCheckParseResult('2026-03-15'),
    );
    $plan = planBuilder()->build($readiness);

    expect($plan->headerStyle)->toBe(HeaderStyle::Clean)
        ->and($plan->showFindings)->toBeFalse()
        ->and($plan->footer)->toBeNull()
        ->and($plan->exitCode)->toBe(0);
});

it('builds skipped plan when tagged file has blockers and an active skipCheck', function (): void {
    $readiness = new ReadinessResult(
        ReadinessLevel::LaravelAdapter,
        true,
        collect(),
        skipCheck: new SkipCheckParseResult('2026-03-15'),
    );
    $plan = planBuilder()->build($readiness);

    expect($plan->headerStyle)->toBe(HeaderStyle::Warning)
        ->and($plan->showFindings)->toBeTrue()
        ->and($plan->footer)->toBe(ReadinessFooter::SkipCheck)
        ->and($plan->exitCode)->toBe(0);
});

it('does not skip a tagged file with a bare skipCheck', function (): void {
    $readiness = new ReadinessResult(
        ReadinessLevel::LaravelAdapter,
        true,
        collect(),
        skipCheck: new SkipCheckParseResult(null),
    );
    $plan = planBuilder()->build($readiness);

    expect($plan->footer)->toBe(ReadinessFooter::AdapterFailed)
        ->and($plan->exitCode)->toBe(1);
});

it('does not skip a tagged file with an expired skipCheck', function (): void {
    $readiness = new ReadinessResult(
        ReadinessLevel::LaravelAdapter,
        true,
        collect(),
        skipCheck: new SkipCheckParseResult('2026-02-28'),
    );
    $plan = planBuilder()->build($readiness);

    expect($plan->footer)->toBe(ReadinessFooter::AdapterFailed)
        ->and($plan->exitCode)->toBe(1);
});

it('does not skip a tagged file with a malformed skipCheck date', function (): void {
    $readiness = new ReadinessResult(
        ReadinessLevel::LaravelAdapter,
        true,
        collect(),
        skipCheck: new SkipCheckParseResult('2026-13-99'),
    );
    $plan = planBuilder()->build($readiness);

    expect($plan->footer)->toBe(ReadinessFooter::AdapterFailed)
        ->and($plan->exitCode)->toBe(1);
});

it('does not skip untagged file even with an active skipCheck', function (): void {
    $readiness = new ReadinessResult(
        ReadinessLevel::Untagged,
        true,
        collect(),
        skipCheck: new SkipCheckParseResult('2026-03-15'),
    );
    $plan = planBuilder()->build($readiness);

    expect($plan->footer)->toBe(ReadinessFooter::NotGuarded)
        ->and($plan->exitCode)->toBe(1);
});

it('does not skip multi-tag file even with an active skipCheck', function (): void {
    $readiness = new ReadinessResult(
        ReadinessLevel::MultiTag,
        true,
        collect(),
        skipCheck: new SkipCheckParseResult('2026-03-15'),
    );
    $plan = planBuilder()->build($readiness);

    expect($plan->footer)->toBe(ReadinessFooter::MultiTagFailed)
        ->and($plan->exitCode)->toBe(1);
});
