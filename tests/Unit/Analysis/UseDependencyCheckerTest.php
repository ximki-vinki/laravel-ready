<?php

declare(strict_types=1);

use LaravelReady\Analysis\AnalysisResult;
use LaravelReady\Analysis\Enums\Tag;
use LaravelReady\Analysis\Findings\TagFinding;
use LaravelReady\Analysis\Findings\UseFinding;
use LaravelReady\Analysis\Findings\UseImportFinding;
use LaravelReady\Analysis\Readiness\ReadinessLevel;
use LaravelReady\Analysis\Readiness\UseDependencyChecker;

covers(UseDependencyChecker::class);

it('does not add use finding for wf import in unguarded file', function (): void {
    $result = new AnalysisResult(collect([
        new UseImportFinding('Wf\Legacy\OldRepo', 5),
    ]));

    $checked = new UseDependencyChecker(appRoot())->check($result, ReadinessLevel::Untagged);

    expect($checked)->toBe($result)
        ->and($checked->findings->filter(
            fn ($finding): bool => $finding instanceof UseFinding,
        ))->toBeEmpty();
});

it('returns same result for guarded file without imports', function (): void {
    $result = new AnalysisResult(collect([
        new TagFinding(Tag::LaravelReady, 3),
    ]));

    $checked = new UseDependencyChecker(appRoot())->check($result, ReadinessLevel::LaravelReady);

    expect($checked)->toBe($result);
});

it('adds use finding when legacy-adapter imports laravel-ready', function (): void {
    $result = new AnalysisResult(collect([
        new TagFinding(Tag::LegacyAdapter, 3),
        new UseImportFinding('App\Domain\TaggedService', 5),
    ]));

    $checked = new UseDependencyChecker(appRoot())->check($result, ReadinessLevel::LegacyAdapter);

    expect($checked->findings)->toContainEqual(new UseFinding('App\Domain\TaggedService', 5));
});

it('preserves original findings when adding use finding', function (): void {
    $import = new UseImportFinding('Wf\Legacy\OldRepo', 5);
    $tag = new TagFinding(Tag::LaravelReady, 3);
    $result = new AnalysisResult(collect([$tag, $import]));

    $checked = new UseDependencyChecker(appRoot())->check($result, ReadinessLevel::LaravelReady);

    expect($checked->findings)->toContainEqual($tag)
        ->and($checked->findings)->toContainEqual($import)
        ->and($checked->findings)->toContainEqual(new UseFinding('Wf\Legacy\OldRepo', 5));
});
