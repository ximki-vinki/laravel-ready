<?php

declare(strict_types=1);

use LaravelReady\Analysis\AnalysisResult;
use LaravelReady\Analysis\Findings\UseFinding;
use LaravelReady\Analysis\Findings\UseImportFinding;
use LaravelReady\Analysis\Readiness\Use\LaravelAdapterUsePolicy;

covers(LaravelAdapterUsePolicy::class);

it('allows wf import', function () {
    $result = new AnalysisResult(collect([
        new UseImportFinding('Wf\Legacy\OldRepo', 5),
    ]));

    expect((new LaravelAdapterUsePolicy(appRoot()))->violations($result))->toBeEmpty();
});

it('allows laravel-adapter app import', function () {
    $result = new AnalysisResult(collect([
        new UseImportFinding('App\Adapter\WfGateway', 5),
    ]));

    expect((new LaravelAdapterUsePolicy(appRoot()))->violations($result))->toBeEmpty();
});

it('allows laravel-adapter app import with class php extension', function () {
    $result = new AnalysisResult(collect([
        new UseImportFinding('App\Domain\LegacyDto', 5),
    ]));

    expect((new LaravelAdapterUsePolicy(appRoot()))->violations($result))->toBeEmpty();
});

it('allows vendor import', function () {
    $result = new AnalysisResult(collect([
        new UseImportFinding('Illuminate\Support\Collection', 5),
    ]));

    expect((new LaravelAdapterUsePolicy(appRoot()))->violations($result))->toBeEmpty();
});

it('denies untagged app import', function () {
    $result = new AnalysisResult(collect([
        new UseImportFinding('App\Domain\UntaggedService', 5),
    ]));

    expect((new LaravelAdapterUsePolicy(appRoot()))->violations($result))
        ->toContainEqual(new UseFinding('App\Domain\UntaggedService', 5));
});

it('denies laravel-ready app import', function () {
    $result = new AnalysisResult(collect([
        new UseImportFinding('App\Domain\TaggedService', 5),
    ]));

    expect((new LaravelAdapterUsePolicy(appRoot()))->violations($result))
        ->toContainEqual(new UseFinding('App\Domain\TaggedService', 5));
});

it('denies multitagged app import', function () {
    $result = new AnalysisResult(collect([
        new UseImportFinding('App\Domain\MultiTaggedService', 5),
    ]));

    expect((new LaravelAdapterUsePolicy(appRoot()))->violations($result))
        ->toContainEqual(new UseFinding('App\Domain\MultiTaggedService', 5));
});

it('denies unresolvable app import', function () {
    $result = new AnalysisResult(collect([
        new UseImportFinding('App\Domain\NonExistent', 5),
    ]));

    expect((new LaravelAdapterUsePolicy(appRoot()))->violations($result))
        ->toContainEqual(new UseFinding('App\Domain\NonExistent', 5));
});
