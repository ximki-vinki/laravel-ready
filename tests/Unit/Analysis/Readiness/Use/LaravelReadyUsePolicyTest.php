<?php

declare(strict_types=1);

use Illuminate\Support\Collection;
use LaravelReady\Analysis\AnalysisResult;
use LaravelReady\Analysis\Findings\UseFinding;
use LaravelReady\Analysis\Findings\UseImportFinding;
use LaravelReady\Analysis\Readiness\Use\LaravelReadyUsePolicy;

covers(LaravelReadyUsePolicy::class);

it('denies wf import', function (): void {
    $result = new AnalysisResult(collect([
        new UseImportFinding('Wf\Legacy\OldRepo', 5),
    ]));

    expect(new LaravelReadyUsePolicy(appRoot())->violations($result))
        ->toContainEqual(new UseFinding('Wf\Legacy\OldRepo', 5));
});

it('denies multiple wf imports', function (): void {
    $result = new AnalysisResult(collect([
        new UseImportFinding('Wf\Legacy\OldRepo', 5),
        new UseImportFinding('Wf\Legacy\AnotherRepo', 7),
    ]));

    expect(new LaravelReadyUsePolicy(appRoot())->violations($result))
        ->toContainEqual(new UseFinding('Wf\Legacy\OldRepo', 5))
        ->toContainEqual(new UseFinding('Wf\Legacy\AnotherRepo', 7));
});

it('allows laravel-ready app import', function (): void {
    $result = new AnalysisResult(collect([
        new UseImportFinding('App\Domain\Invoice', 5),
    ]));

    expect(new LaravelReadyUsePolicy(appRoot())->violations($result))->toBeEmpty();
});

it('allows vendor import', function (): void {
    $result = new AnalysisResult(collect([
        new UseImportFinding(Collection::class, 5),
    ]));

    expect(new LaravelReadyUsePolicy(appRoot())->violations($result))->toBeEmpty();
});

it('denies untagged app import', function (): void {
    $result = new AnalysisResult(collect([
        new UseImportFinding('App\Domain\UntaggedService', 5),
    ]));

    expect(new LaravelReadyUsePolicy(appRoot())->violations($result))
        ->toContainEqual(new UseFinding('App\Domain\UntaggedService', 5));
});

it('denies unresolvable app import', function (): void {
    $result = new AnalysisResult(collect([
        new UseImportFinding('App\Domain\NonExistent', 5),
    ]));

    expect(new LaravelReadyUsePolicy(appRoot())->violations($result))
        ->toContainEqual(new UseFinding('App\Domain\NonExistent', 5));
});

it('denies multitagged app import', function (): void {
    $result = new AnalysisResult(collect([
        new UseImportFinding('App\Domain\MultiTaggedService', 5),
    ]));

    expect(new LaravelReadyUsePolicy(appRoot())->violations($result))
        ->toContainEqual(new UseFinding('App\Domain\MultiTaggedService', 5));
});

it('allows tagged laravel-ready app import', function (): void {
    $result = new AnalysisResult(collect([
        new UseImportFinding('App\Domain\TaggedService', 5),
    ]));

    expect(new LaravelReadyUsePolicy(appRoot())->violations($result))->toBeEmpty();
});

it('allows another laravel-ready app import', function (): void {
    $result = new AnalysisResult(collect([
        new UseImportFinding('App\Domain\ReadyService', 5),
    ]));

    expect(new LaravelReadyUsePolicy(appRoot())->violations($result))->toBeEmpty();
});

it('denies app import with class php extension only', function (): void {
    $result = new AnalysisResult(collect([
        new UseImportFinding('App\Domain\LegacyDto', 5),
    ]));

    expect(new LaravelReadyUsePolicy(appRoot())->violations($result))
        ->toContainEqual(new UseFinding('App\Domain\LegacyDto', 5));
});

it('allows laravel-adapter app import', function (): void {
    $result = new AnalysisResult(collect([
        new UseImportFinding('App\Adapter\WfGateway', 5),
    ]));

    expect(new LaravelReadyUsePolicy(appRoot())->violations($result))->toBeEmpty();
});
