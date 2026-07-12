<?php

declare(strict_types=1);

use LaravelReady\Analysis\AnalysisResult;
use LaravelReady\Analysis\Enums\Tag;
use LaravelReady\Analysis\Findings\TagFinding;
use LaravelReady\Analysis\Readiness\ReadinessLevel;
use LaravelReady\Analysis\Readiness\ReadinessLevelResolver;

covers(ReadinessLevelResolver::class);

it('resolves untagged when no tags', function (): void {
    $result = new AnalysisResult(collect());

    expect((new ReadinessLevelResolver)->fromResult($result))->toBe(ReadinessLevel::Untagged);
});

it('resolves laravel ready for laravel-ready tag', function (): void {
    $result = new AnalysisResult(collect([new TagFinding(Tag::LaravelReady, 3)]));

    expect((new ReadinessLevelResolver)->fromResult($result))->toBe(ReadinessLevel::LaravelReady);
});

it('resolves laravel adapter for laravel-adapter tag', function (): void {
    $result = new AnalysisResult(collect([new TagFinding(Tag::LaravelAdapter, 3)]));

    expect((new ReadinessLevelResolver)->fromResult($result))->toBe(ReadinessLevel::LaravelAdapter);
});

it('resolves legacy adapter for legacy-adapter tag', function (): void {
    $result = new AnalysisResult(collect([new TagFinding(Tag::LegacyAdapter, 3)]));

    expect((new ReadinessLevelResolver)->fromResult($result))->toBe(ReadinessLevel::LegacyAdapter);
});

it('resolves legacy perfect for legacy-perfect tag', function (): void {
    $result = new AnalysisResult(collect([new TagFinding(Tag::LegacyPerfect, 3)]));

    expect((new ReadinessLevelResolver)->fromResult($result))->toBe(ReadinessLevel::LegacyPerfect);
});

it('resolves legacy for legacy-code tag', function (): void {
    $result = new AnalysisResult(collect([new TagFinding(Tag::Legacy, 4)]));

    expect((new ReadinessLevelResolver)->fromResult($result))->toBe(ReadinessLevel::Legacy);
});

it('resolves multitag when multiple tags are present', function (): void {
    $result = new AnalysisResult(collect([
        new TagFinding(Tag::LaravelReady, 3),
        new TagFinding(Tag::Legacy, 10),
    ]));

    expect((new ReadinessLevelResolver)->fromResult($result))->toBe(ReadinessLevel::MultiTag);
});
