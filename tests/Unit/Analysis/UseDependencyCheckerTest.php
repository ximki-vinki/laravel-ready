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

it('adds use finding for wf import in guarded file', closure: function () {
    $result = new AnalysisResult(collect([
        new TagFinding(Tag::LaravelReady, 3),
        new UseImportFinding('Wf\Legacy\OldRepo', 5),
    ]));

    $checked = new UseDependencyChecker(appRoot())->check($result, ReadinessLevel::LaravelReady);

    expect($checked->findings)->toContainEqual(new UseFinding('Wf\Legacy\OldRepo', 5));
});

it('adds use findings for multiple wf imports in guarded file', function () {
    $result = new AnalysisResult(collect([
        new TagFinding(Tag::LaravelReady, 3),
        new UseImportFinding('Wf\Legacy\OldRepo', 5),
        new UseImportFinding('Wf\Legacy\AnotherRepo', 7),
    ]));

    $checked = new UseDependencyChecker(appRoot())->check($result, ReadinessLevel::LaravelReady);

    expect($checked->findings)->toContainEqual(new UseFinding('Wf\Legacy\OldRepo', 5))
        ->and($checked->findings)->toContainEqual(new UseFinding('Wf\Legacy\AnotherRepo', 7));
});

it('does not add use finding for wf import in laravel-adapter file', function () {
    $result = new AnalysisResult(collect([
        new TagFinding(Tag::LaravelAdapter, 3),
        new UseImportFinding('Wf\Legacy\OldRepo', 5),
    ]));

    $checked = (new UseDependencyChecker(appRoot()))->check($result, ReadinessLevel::LaravelAdapter);

    expect($checked)->toBe($result)
        ->and($checked->findings->filter(
            fn ($finding): bool => $finding instanceof UseFinding,
        ))->toBeEmpty();
});

it('adds use finding when laravel-adapter file imports untagged app class', function () {
    $result = new AnalysisResult(collect([
        new TagFinding(Tag::LaravelAdapter, 7),
        new UseImportFinding('App\Domain\UntaggedService', 5),
    ]));

    $checked = new UseDependencyChecker(appRoot())->check($result, ReadinessLevel::LaravelAdapter);

    expect($checked->findings)->toContainEqual(new UseFinding('App\Domain\UntaggedService', 5));
});

it('does not add use finding when laravel-adapter file imports another laravel-adapter class', function () {
    $result = new AnalysisResult(collect([
        new TagFinding(Tag::LaravelAdapter, 7),
        new UseImportFinding('App\Adapter\WfGateway', 5),
    ]));

    $checked = new UseDependencyChecker(appRoot())->check($result, ReadinessLevel::LaravelAdapter);

    expect($checked)->toBe($result)
        ->and($checked->findings->filter(
            fn ($finding): bool => $finding instanceof UseFinding,
        ))->toBeEmpty();
});

it('does not add use finding for wf import in unguarded file', function () {
    $result = new AnalysisResult(collect([
        new UseImportFinding('Wf\Legacy\OldRepo', 5),
    ]));

    $checked = (new UseDependencyChecker(appRoot()))->check($result, ReadinessLevel::Untagged);

    expect($checked)->toBe($result)
        ->and($checked->findings->filter(
            fn ($finding): bool => $finding instanceof UseFinding,
        ))->toBeEmpty();
});

it('does not add use finding for non wf import in guarded file', function () {
    $result = new AnalysisResult(collect([
        new TagFinding(Tag::LaravelReady, 3),
        new UseImportFinding('App\Domain\Invoice', 5),
    ]));

    $checked = (new UseDependencyChecker(appRoot()))->check($result, ReadinessLevel::LaravelReady);

    expect($checked)->toBe($result)
        ->and($checked->findings->filter(
            fn ($finding): bool => $finding instanceof UseFinding,
        ))->toBeEmpty();
});

it('does not add use finding when guarded file imports vendor class with project root', function () {
    $result = new AnalysisResult(collect([
        new TagFinding(Tag::LaravelReady, 7),
        new UseImportFinding('Illuminate\Support\Collection', 5),
    ]));

    $checked = new UseDependencyChecker(appRoot())->check($result, ReadinessLevel::LaravelReady);

    expect($checked)->toBe($result)
        ->and($checked->findings->filter(
            fn ($finding): bool => $finding instanceof UseFinding,
        ))->toBeEmpty();
});

it('returns same result for guarded file without imports', function () {
    $result = new AnalysisResult(collect([
        new TagFinding(Tag::LaravelReady, 3),
    ]));

    $checked = (new UseDependencyChecker(appRoot()))->check($result, ReadinessLevel::LaravelReady);

    expect($checked)->toBe($result);
});

it('preserves original findings when adding use finding', function () {
    $import = new UseImportFinding('Wf\Legacy\OldRepo', 5);
    $tag = new TagFinding(Tag::LaravelReady, 3);
    $result = new AnalysisResult(collect([$tag, $import]));

    $checked = (new UseDependencyChecker(appRoot()))->check($result, ReadinessLevel::LaravelReady);

    expect($checked->findings)->toContainEqual($tag)
        ->and($checked->findings)->toContainEqual($import)
        ->and($checked->findings)->toContainEqual(new UseFinding('Wf\Legacy\OldRepo', 5));
});

it('adds use finding when guarded file imports untagged app class', function () {
    $result = new AnalysisResult(collect([
        new TagFinding(Tag::LaravelReady, 7),
        new UseImportFinding('App\Domain\UntaggedService', 5),
    ]));

    $checked = new UseDependencyChecker(appRoot())->check($result, ReadinessLevel::LaravelReady);

    expect($checked->findings)->toContainEqual(new UseFinding('App\Domain\UntaggedService', 5));
});

it('adds use finding when guarded file imports unresolvable app class', function () {
    $result = new AnalysisResult(collect([
        new TagFinding(Tag::LaravelReady, 7),
        new UseImportFinding('App\Domain\NonExistent', 5),
    ]));

    $checked = new UseDependencyChecker(appRoot())->check($result, ReadinessLevel::LaravelReady);

    expect($checked->findings)->toContainEqual(new UseFinding('App\Domain\NonExistent', 5));
});

it('does not add use finding when guarded file imports laravel-ready class from project app', function () {
    $result = new AnalysisResult(collect([
        new TagFinding(Tag::LaravelReady, 7),
        new UseImportFinding('App\Domain\TaggedService', 5),
    ]));

    $checked = new UseDependencyChecker(appRoot())->check($result, ReadinessLevel::LaravelReady);

    expect($checked)->toBe($result)
        ->and($checked->findings->filter(
            fn ($finding): bool => $finding instanceof UseFinding,
        ))->toBeEmpty();
});

it('does not add use finding when guarded file imports another laravel-ready app class', function () {
    $result = new AnalysisResult(collect([
        new TagFinding(Tag::LaravelReady, 7),
        new UseImportFinding('App\Domain\ReadyService', 5),
    ]));

    $checked = new UseDependencyChecker(appRoot())->check($result, ReadinessLevel::LaravelReady);

    expect($checked)->toBe($result)
        ->and($checked->findings->filter(
            fn ($finding): bool => $finding instanceof UseFinding,
        ))->toBeEmpty();
});

it('does not add use finding when guarded file imports laravel-adapter class with class php extension', function () {
    $result = new AnalysisResult(collect([
        new TagFinding(Tag::LaravelReady, 7),
        new UseImportFinding('App\Domain\LegacyDto', 5),
    ]));

    $checked = new UseDependencyChecker(appRoot())->check($result, ReadinessLevel::LaravelReady);

    expect($checked)->toBe($result)
        ->and($checked->findings->filter(
            fn ($finding): bool => $finding instanceof UseFinding,
        ))->toBeEmpty();
});

it('does not add use finding when guarded file imports laravel-adapter class', function () {
    $result = new AnalysisResult(collect([
        new TagFinding(Tag::LaravelReady, 7),
        new UseImportFinding('App\Adapter\WfGateway', 5),
    ]));

    $checked = new UseDependencyChecker(appRoot())->check($result, ReadinessLevel::LaravelReady);

    expect($checked)->toBe($result)
        ->and($checked->findings->filter(
            fn ($finding): bool => $finding instanceof UseFinding,
        ))->toBeEmpty();
});
