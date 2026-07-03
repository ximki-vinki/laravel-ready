<?php

declare(strict_types=1);

use LaravelReady\Analysis\AnalysisResult;
use LaravelReady\Analysis\BlockedFunction;
use LaravelReady\Analysis\Detector;
use LaravelReady\Analysis\FunctionCallFinding;
use LaravelReady\Analysis\ReadinessLevel;
use LaravelReady\Analysis\ReadinessResolver;
use LaravelReady\Analysis\SuperglobalFinding;
use LaravelReady\Analysis\SuperglobalName;
use LaravelReady\Analysis\Tag;
use LaravelReady\Analysis\TagFinding;

covers(ReadinessResolver::class);

it('resolves untagged for clean analysis result without tag', function () {
    $result = new AnalysisResult(collect());

    $readiness = (new ReadinessResolver)->resolve($result);

    expect($readiness->actual)->toBe(ReadinessLevel::Untagged)
        ->and($readiness->findings)->toBeEmpty()
        ->and($readiness->hasBlockers)->toBeTrue();
});

it('resolves laravel ready for laravel-ready tag without blockers', function () {
    $result = new AnalysisResult(collect([new TagFinding(Tag::LaravelReady, 3)]));

    $readiness = (new ReadinessResolver)->resolve($result);

    expect($readiness->actual)->toBe(ReadinessLevel::LaravelReady)
        ->and($readiness->hasBlockers)->toBeFalse();
});

it('resolves untagged when analysis result has only legacy finding', function () {
    $findings = collect([new SuperglobalFinding(SuperglobalName::Get, 3)]);
    $result = new AnalysisResult($findings);

    $readiness = (new ReadinessResolver)->resolve($result);

    expect($readiness->actual)->toBe(ReadinessLevel::Untagged)
        ->and($readiness->findings)->toBe($findings)
        ->and($readiness->hasBlockers)->toBeTrue();
});

it('resolves legacy when analysis result has legacy-code tag', function () {
    $result = new AnalysisResult(collect([new TagFinding(Tag::Legacy, 4)]));

    $readiness = (new ReadinessResolver)->resolve($result);

    expect($readiness->actual)->toBe(ReadinessLevel::Legacy)
        ->and($readiness->hasBlockers)->toBeFalse();
});

it('resolves multitag when analysis result has multiple tags', function () {
    $result = new AnalysisResult(collect([
        new TagFinding(Tag::LaravelReady, 3),
        new TagFinding(Tag::Legacy, 10),
    ]));

    $readiness = (new ReadinessResolver)->resolve($result);

    expect($readiness->actual)->toBe(ReadinessLevel::MultiTag)
        ->and($readiness->hasBlockers)->toBeTrue();
});

it('detects blockers when laravel-ready tag is paired with legacy finding', function () {
    $result = new AnalysisResult(collect([
        new FunctionCallFinding(BlockedFunction::Define, 4),
        new TagFinding(Tag::LaravelReady, 3),
    ]));

    $readiness = (new ReadinessResolver)->resolve($result);

    expect($readiness->actual)->toBe(ReadinessLevel::LaravelReady)
        ->and($readiness->hasBlockers)->toBeTrue();
});

it('does not block legacy-code tag with legacy finding', function () {
    $result = new AnalysisResult(collect([
        new SuperglobalFinding(SuperglobalName::Get, 5),
        new TagFinding(Tag::Legacy, 4),
    ]));

    $readiness = (new ReadinessResolver)->resolve($result);

    expect($readiness->actual)->toBe(ReadinessLevel::Legacy)
        ->and($readiness->hasBlockers)->toBeFalse();
});

it('detects blockers when guarded file imports wf namespace', function () {
    $path = fixture('Use/src/Domain/Invoice.php');

    $result = (new Detector)->analyse($path);
    $readiness = (new ReadinessResolver)->resolve($result);

    expect($readiness->hasBlockers)->toBeTrue();
});
