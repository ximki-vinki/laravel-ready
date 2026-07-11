<?php

declare(strict_types=1);

use Illuminate\Support\Collection;
use LaravelReady\Analysis\Findings\UseImportFinding;
use LaravelReady\Analysis\Readiness\ReadinessLevel;
use LaravelReady\Analysis\Readiness\Use\Rule\DenyAppImportByLevelRule;

covers(DenyAppImportByLevelRule::class);

it('allows vendor import', function (): void {
    $import = new UseImportFinding(Collection::class, 5);
    $rule = new DenyAppImportByLevelRule(appRoot(), [ReadinessLevel::LaravelReady])->isDenied($import);

    expect($rule)->toBeFalse();
});

it('denies unresolvable app import', function (): void {
    $import = new UseImportFinding('App\Domain\NonExistent', 5);
    $rule = new DenyAppImportByLevelRule(appRoot(), [ReadinessLevel::LaravelReady])->isDenied($import);

    expect($rule)->toBeTrue();
});

it('allows tagged laravel-ready app import', function (): void {
    $import = new UseImportFinding('App\Domain\TaggedService', 5);
    $rule = new DenyAppImportByLevelRule(appRoot(), [ReadinessLevel::LaravelReady])->isDenied($import);

    expect($rule)->toBeFalse();
});

it('denies laravel-ready app import when only adapter is allowed', function (): void {
    $import = new UseImportFinding('App\Domain\TaggedService', 5);
    $rule = new DenyAppImportByLevelRule(appRoot(), [ReadinessLevel::LaravelAdapter])->isDenied($import);

    expect($rule)->toBeTrue();
});

it('allows laravel-adapter app import with class php extension', function (): void {
    $import = new UseImportFinding('App\Domain\LegacyDto', 5);
    $rule = new DenyAppImportByLevelRule(appRoot(), [ReadinessLevel::LaravelAdapter], ['.class.php'])->isDenied($import);

    expect($rule)->toBeFalse();
});

it('denies app import with class php extension only', function (): void {
    $import = new UseImportFinding('App\Domain\LegacyDto', 5);
    $rule = new DenyAppImportByLevelRule(appRoot(), [ReadinessLevel::LaravelReady])->isDenied($import);

    expect($rule)->toBeTrue();
});
