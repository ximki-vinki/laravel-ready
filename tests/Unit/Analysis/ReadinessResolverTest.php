<?php

declare(strict_types=1);

use LaravelReady\Analysis\AnalysisResult;
use LaravelReady\Analysis\BlockedFunction;
use LaravelReady\Analysis\FunctionCallFinding;
use LaravelReady\Analysis\ReadinessLevel;
use LaravelReady\Analysis\ReadinessResolver;
use LaravelReady\Analysis\SuperglobalFinding;
use LaravelReady\Analysis\SuperglobalName;
use LaravelReady\Analysis\Tag;

covers(ReadinessResolver::class);

it('resolves laravel ready for clean analysis result', function () {
    $result = new AnalysisResult(collect());

    $readiness = (new ReadinessResolver)->resolve($result);

    expect($readiness->actual)->toBe(ReadinessLevel::LaravelReady)
        ->and($readiness->findings)->toBeEmpty();
});

it('has no pledged and no guard failed for clean analysis result without tag', function () {
    $result = new AnalysisResult(collect());

    $readiness = (new ReadinessResolver)->resolve($result);

    expect($readiness->actual)->toBe(ReadinessLevel::LaravelReady)
        ->and($readiness->pledged)->toBeNull()
        ->and($readiness->guardFailed)->toBeFalse();
});

it('sets pledged laravel ready for laravel-ready tag without legacy findings', function () {
    $result = new AnalysisResult(collect(), Tag::LaravelReady);

    $readiness = (new ReadinessResolver)->resolve($result);

    expect($readiness->pledged)->toBe(ReadinessLevel::LaravelReady)
        ->and($readiness->guardFailed)->toBeFalse();
});

it('resolves legacy when analysis result has legacy finding', function () {
    $findings = collect([new SuperglobalFinding(SuperglobalName::Get, 3)]);
    $result = new AnalysisResult($findings);

    $readiness = (new ReadinessResolver)->resolve($result);

    expect($readiness->actual)->toBe(ReadinessLevel::Legacy)
        ->and($readiness->findings)->toBe($findings);
});

it('resolves laravel ready when analysis result has laravel-ready tag without legacy findings', function () {
    $result = new AnalysisResult(collect(), Tag::LaravelReady);

    $readiness = (new ReadinessResolver)->resolve($result);

    expect($readiness->actual)->toBe(ReadinessLevel::LaravelReady);
});

it('resolves legacy when laravel-ready tag is paired with legacy finding', function () {
    $findings = collect([new FunctionCallFinding(BlockedFunction::Define, 4)]);
    $result = new AnalysisResult($findings, Tag::LaravelReady);

    $readiness = (new ReadinessResolver)->resolve($result);

    expect($readiness->actual)->toBe(ReadinessLevel::Legacy);
});
