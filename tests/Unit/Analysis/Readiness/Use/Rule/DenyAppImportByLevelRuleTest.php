<?php

declare(strict_types=1);

use LaravelReady\Analysis\Findings\UseImportFinding;
use LaravelReady\Analysis\Readiness\ReadinessLevel;
use LaravelReady\Analysis\Readiness\Use\Rule\DenyAppImportByLevelRule;
use LaravelReady\Analysis\Resolution\Psr4ClassLocator;
use LaravelReady\Project\NamespaceResolver;

covers(DenyAppImportByLevelRule::class);

it('allows vendor import', function (): void {
    $import = new UseImportFinding('Vendor\Package\Service', 5);
    $locator = new Psr4ClassLocator(collect([
        new NamespaceResolver('App\\', appRoot()),
    ]));
    $rule = new DenyAppImportByLevelRule($locator, [ReadinessLevel::LaravelReady])->isDenied($import);

    expect($rule)->toBeFalse();
});

it('allows unresolvable app import', function (): void {
    $import = new UseImportFinding('App\Domain\NonExistent', 5);
    $locator = new Psr4ClassLocator(collect([
        new NamespaceResolver('App\\', appRoot()),
    ]));
    $rule = new DenyAppImportByLevelRule($locator, [ReadinessLevel::LaravelReady])->isDenied($import);

    expect($rule)->toBeFalse();
});

it('allows tagged laravel-ready app import', function (): void {
    $import = new UseImportFinding('App\Domain\TaggedService', 5);
    $locator = new Psr4ClassLocator(collect([
        new NamespaceResolver('App\\', appRoot()),
    ]));
    $rule = new DenyAppImportByLevelRule($locator, [ReadinessLevel::LaravelReady])->isDenied($import);

    expect($rule)->toBeFalse();
});

it('denies laravel-ready app import when only adapter is allowed', function (): void {
    $import = new UseImportFinding('App\Domain\TaggedService', 5);
    $locator = new Psr4ClassLocator(collect([
        new NamespaceResolver('App\\', appRoot()),
    ]));
    $rule = new DenyAppImportByLevelRule($locator, [ReadinessLevel::LaravelAdapter])->isDenied($import);

    expect($rule)->toBeTrue();
});

it('allows laravel-adapter app import with class php extension', function (): void {
    $import = new UseImportFinding('App\Domain\LegacyDto', 5);
    $locator = new Psr4ClassLocator(collect([
        new NamespaceResolver('App\\', appRoot()),
    ]));
    $rule = new DenyAppImportByLevelRule($locator, [ReadinessLevel::LaravelAdapter])->isDenied($import);

    expect($rule)->toBeFalse();
});

it('denies class php import when dependency level is not allowed', function (): void {
    $import = new UseImportFinding('App\Domain\LegacyDto', 5);
    $locator = new Psr4ClassLocator(collect([
        new NamespaceResolver('App\\', appRoot()),
    ]));
    $rule = new DenyAppImportByLevelRule($locator, [ReadinessLevel::LaravelReady])->isDenied($import);

    expect($rule)->toBeTrue();
});
