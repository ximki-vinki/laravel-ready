<?php

declare(strict_types=1);

use LaravelReady\Analysis\AnalysisResult;
use LaravelReady\Analysis\Detector;
use LaravelReady\Analysis\Enums\BlockedFunction;
use LaravelReady\Analysis\Enums\SuperglobalName;
use LaravelReady\Analysis\Enums\Tag;
use LaravelReady\Analysis\Findings\FunctionCallFinding;
use LaravelReady\Analysis\Findings\SuperglobalFinding;
use LaravelReady\Analysis\Findings\TagFinding;
use LaravelReady\Analysis\Findings\UseFinding;
use LaravelReady\Analysis\Readiness\ReadinessLevel;
use LaravelReady\Analysis\Readiness\ReadinessResolver;

covers(ReadinessResolver::class);

it('resolves untagged for clean analysis result without tag', function () {
    $result = new AnalysisResult(collect());

    $readiness = (new ReadinessResolver)->resolve($result, appRoot());

    expect($readiness->actual)->toBe(ReadinessLevel::Untagged)
        ->and($readiness->findings)->toBeEmpty()
        ->and($readiness->hasBlockers)->toBeTrue();
});

it('resolves laravel ready for laravel-ready tag without blockers', function () {
    $result = new AnalysisResult(collect([new TagFinding(Tag::LaravelReady, 3)]));

    $readiness = (new ReadinessResolver)->resolve($result, appRoot());

    expect($readiness->actual)->toBe(ReadinessLevel::LaravelReady)
        ->and($readiness->hasBlockers)->toBeFalse();
});

it('resolves laravel adapter for laravel-adapter tag without blockers', function () {
    $result = new AnalysisResult(collect([new TagFinding(Tag::LaravelAdapter, 3)]));

    $readiness = (new ReadinessResolver)->resolve($result, appRoot());

    expect($readiness->actual)->toBe(ReadinessLevel::LaravelAdapter)
        ->and($readiness->hasBlockers)->toBeFalse();
});

it('resolves untagged when analysis result has only legacy finding', function () {
    $findings = collect([new SuperglobalFinding(SuperglobalName::Get, 3)]);
    $result = new AnalysisResult($findings);

    $readiness = (new ReadinessResolver)->resolve($result, appRoot());

    expect($readiness->actual)->toBe(ReadinessLevel::Untagged)
        ->and($readiness->findings)->toBe($findings)
        ->and($readiness->hasBlockers)->toBeTrue();
});

it('resolves legacy when analysis result has legacy-code tag', function () {
    $result = new AnalysisResult(collect([new TagFinding(Tag::Legacy, 4)]));

    $readiness = (new ReadinessResolver)->resolve($result, appRoot());

    expect($readiness->actual)->toBe(ReadinessLevel::Legacy)
        ->and($readiness->hasBlockers)->toBeFalse();
});

it('resolves multitag when analysis result has multiple tags', function () {
    $result = new AnalysisResult(collect([
        new TagFinding(Tag::LaravelReady, 3),
        new TagFinding(Tag::Legacy, 10),
    ]));

    $readiness = (new ReadinessResolver)->resolve($result, appRoot());

    expect($readiness->actual)->toBe(ReadinessLevel::MultiTag)
        ->and($readiness->hasBlockers)->toBeTrue();
});

it('detects blockers when laravel-ready tag is paired with legacy finding', function () {
    $result = new AnalysisResult(collect([
        new FunctionCallFinding(BlockedFunction::Define, 4),
        new TagFinding(Tag::LaravelReady, 3),
    ]));

    $readiness = (new ReadinessResolver)->resolve($result, appRoot());

    expect($readiness->actual)->toBe(ReadinessLevel::LaravelReady)
        ->and($readiness->hasBlockers)->toBeTrue();
});

it('does not block legacy-code tag with legacy finding', function () {
    $result = new AnalysisResult(collect([
        new SuperglobalFinding(SuperglobalName::Get, 5),
        new TagFinding(Tag::Legacy, 4),
    ]));

    $readiness = (new ReadinessResolver)->resolve($result, appRoot());

    expect($readiness->actual)->toBe(ReadinessLevel::Legacy)
        ->and($readiness->hasBlockers)->toBeFalse();
});

it('detects blockers when laravel-adapter tag is paired with legacy finding', function () {
    $result = new AnalysisResult(collect([
        new SuperglobalFinding(SuperglobalName::Get, 5),
        new TagFinding(Tag::LaravelAdapter, 3),
    ]));

    $readiness = (new ReadinessResolver)->resolve($result, appRoot());

    expect($readiness->actual)->toBe(ReadinessLevel::LaravelAdapter)
        ->and($readiness->hasBlockers)->toBeTrue();
});

it('does not block laravel-adapter tag with use finding only', function () {
    $result = new AnalysisResult(collect([
        new TagFinding(Tag::LaravelAdapter, 3),
        new UseFinding('Wf\Legacy\OldRepo', 5),
    ]));

    $readiness = (new ReadinessResolver)->resolve($result, appRoot());

    expect($readiness->actual)->toBe(ReadinessLevel::LaravelAdapter)
        ->and($readiness->hasBlockers)->toBeFalse();
});

it('detects blockers when guarded file imports wf namespace', function () {
    $path = fixture('Use/project/app/Domain/Invoice.php');

    $result = (new Detector)->analyse($path);
    $readiness = (new ReadinessResolver)->resolve($result, appRoot());

    expect($readiness->hasBlockers)->toBeTrue()
        ->and($readiness->findings)->toContainEqual(new UseFinding('Wf\Legacy\OldRepo', 5));
});
