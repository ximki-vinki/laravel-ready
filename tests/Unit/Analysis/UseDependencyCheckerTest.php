<?php

declare(strict_types=1);

use LaravelReady\Analysis\AnalysisResult;
use LaravelReady\Analysis\Detector;
use LaravelReady\Analysis\Tag;
use LaravelReady\Analysis\TagFinding;
use LaravelReady\Analysis\UseDependencyChecker;
use LaravelReady\Analysis\UseFinding;
use LaravelReady\Analysis\UseImportFinding;

covers(UseDependencyChecker::class);

it('adds use finding for wf import in guarded file', function () {
    $result = new AnalysisResult(collect([
        new TagFinding(Tag::LaravelReady, 3),
        new UseImportFinding('Wf\Legacy\OldRepo', 5),
    ]));

    $checked = (new UseDependencyChecker)->check($result);

    expect($checked->findings)->toContainEqual(new UseFinding('Wf\Legacy\OldRepo', 5));
});

it('does not add use finding for wf import in unguarded file', function () {
    $result = new AnalysisResult(collect([
        new UseImportFinding('Wf\Legacy\OldRepo', 5),
    ]));

    $checked = (new UseDependencyChecker)->check($result);

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

    $checked = (new UseDependencyChecker)->check($result);

    expect($checked)->toBe($result)
        ->and($checked->findings->filter(
            fn ($finding): bool => $finding instanceof UseFinding,
        ))->toBeEmpty();
});

it('returns same result for guarded file without imports', function () {
    $result = new AnalysisResult(collect([
        new TagFinding(Tag::LaravelReady, 3),
    ]));

    $checked = (new UseDependencyChecker)->check($result);

    expect($checked)->toBe($result);
});

it('preserves original findings when adding use finding', function () {
    $import = new UseImportFinding('Wf\Legacy\OldRepo', 5);
    $tag = new TagFinding(Tag::LaravelReady, 3);
    $result = new AnalysisResult(collect([$tag, $import]));

    $checked = (new UseDependencyChecker)->check($result);

    expect($checked->findings)->toContainEqual($tag)
        ->and($checked->findings)->toContainEqual($import)
        ->and($checked->findings)->toContainEqual(new UseFinding('Wf\Legacy\OldRepo', 5));
});

it('detects wf import from detector output on guarded fixture', function () {
    $result = (new Detector)->analyse(fixture('Use/src/Domain/Invoice.php'));

    $checked = (new UseDependencyChecker)->check($result);

    expect($checked->findings)->toContainEqual(new UseFinding('Wf\Legacy\OldRepo', 5));
});
