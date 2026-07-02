<?php

declare(strict_types=1);

use LaravelReady\Analysis\AnalysisResult;
use LaravelReady\Analysis\FunctionCallFinding;
use LaravelReady\Analysis\BlockedFunction;
use LaravelReady\Analysis\ReadinessLevel;
use LaravelReady\Analysis\ReadinessResolver;
use LaravelReady\Analysis\SuperglobalFinding;
use LaravelReady\Analysis\SuperglobalName;
use LaravelReady\Analysis\Tag;

covers(ReadinessResolver::class);

it('resolves laravel ready for clean analysis result', function () {
    $result = new AnalysisResult(collect());

    $readiness = (new ReadinessResolver)->resolve($result);

    expect($readiness->level)->toBe(ReadinessLevel::LaravelReady)
        ->and($readiness->findings)->toBeEmpty();
});

it('resolves legacy when analysis result has legacy finding', function () {
    $findings = collect([new SuperglobalFinding(SuperglobalName::Get, 3)]);
    $result = new AnalysisResult($findings);

    $readiness = (new ReadinessResolver)->resolve($result);

    expect($readiness->level)->toBe(ReadinessLevel::Legacy)
        ->and($readiness->findings)->toBe($findings);
});

it('resolves laravel ready when analysis result has laravel-ready tag without legacy findings', function () {
    $result = new AnalysisResult(collect(), Tag::LaravelReady);

    $readiness = (new ReadinessResolver)->resolve($result);

    expect($readiness->level)->toBe(ReadinessLevel::LaravelReady);
});

it('resolves legacy when laravel-ready tag is paired with legacy finding', function () {
    $findings = collect([new FunctionCallFinding(BlockedFunction::Define, 4)]);
    $result = new AnalysisResult($findings, Tag::LaravelReady);

    $readiness = (new ReadinessResolver)->resolve($result);

    expect($readiness->level)->toBe(ReadinessLevel::Legacy);
});
