<?php

declare(strict_types=1);

use Illuminate\Support\Collection;
use LaravelReady\Analysis\AnalysisResult;
use LaravelReady\Analysis\Findings\UseFinding;
use LaravelReady\Analysis\Findings\UseImportFinding;
use LaravelReady\Analysis\Readiness\Use\LegacyPerfectUsePolicy;

covers(LegacyPerfectUsePolicy::class);

it('allows wf import', function (): void {
    $result = new AnalysisResult(collect([
        new UseImportFinding('Wf\Legacy\OldRepo', 5),
    ]));

    expect(new LegacyPerfectUsePolicy(appRoot())->violations($result))->toBeEmpty();
});

it('allows legacy-adapter app import', function (): void {
    $result = new AnalysisResult(collect([
        new UseImportFinding('App\Adapter\LegacyBridge', 5),
    ]));

    expect(new LegacyPerfectUsePolicy(appRoot())->violations($result))->toBeEmpty();
});

it('allows legacy-perfect app import', function (): void {
    $result = new AnalysisResult(collect([
        new UseImportFinding('App\Domain\PerfectService', 5),
    ]));

    expect(new LegacyPerfectUsePolicy(appRoot())->violations($result))->toBeEmpty();
});

it('allows vendor import', function (): void {
    $result = new AnalysisResult(collect([
        new UseImportFinding(Collection::class, 5),
    ]));

    expect(new LegacyPerfectUsePolicy(appRoot())->violations($result))->toBeEmpty();
});

it('denies legacy-code app import', function (): void {
    $result = new AnalysisResult(collect([
        new UseImportFinding('App\Domain\LegacyService', 5),
    ]));

    expect(new LegacyPerfectUsePolicy(appRoot())->violations($result))
        ->toContainEqual(new UseFinding('App\Domain\LegacyService', 5));
});

it('denies laravel-ready app import', function (): void {
    $result = new AnalysisResult(collect([
        new UseImportFinding('App\Domain\TaggedService', 5),
    ]));

    expect(new LegacyPerfectUsePolicy(appRoot())->violations($result))
        ->toContainEqual(new UseFinding('App\Domain\TaggedService', 5));
});

it('denies laravel-adapter app import', function (): void {
    $result = new AnalysisResult(collect([
        new UseImportFinding('App\Adapter\WfGateway', 5),
    ]));

    expect(new LegacyPerfectUsePolicy(appRoot())->violations($result))
        ->toContainEqual(new UseFinding('App\Adapter\WfGateway', 5));
});

it('denies untagged app import', function (): void {
    $result = new AnalysisResult(collect([
        new UseImportFinding('App\Domain\UntaggedService', 5),
    ]));

    expect(new LegacyPerfectUsePolicy(appRoot())->violations($result))
        ->toContainEqual(new UseFinding('App\Domain\UntaggedService', 5));
});

it('denies unresolvable app import', function (): void {
    $result = new AnalysisResult(collect([
        new UseImportFinding('App\Domain\NonExistent', 5),
    ]));

    expect(new LegacyPerfectUsePolicy(appRoot())->violations($result))
        ->toContainEqual(new UseFinding('App\Domain\NonExistent', 5));
});
