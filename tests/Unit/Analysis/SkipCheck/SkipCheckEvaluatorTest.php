<?php

declare(strict_types=1);

use Illuminate\Support\Carbon;
use LaravelReady\Analysis\SkipCheck\SkipCheckEvaluator;
use LaravelReady\Analysis\SkipCheck\SkipCheckParseResult;

covers(SkipCheckEvaluator::class);

it('applies a future absolute date', function (): void {
    $applies = new SkipCheckEvaluator(Carbon::parse('2026-03-01'))
        ->applies(new SkipCheckParseResult('2026-03-15'));

    expect($applies)->toBeTrue();
});

it('applies on the expiry date itself', function (): void {
    $applies = new SkipCheckEvaluator(Carbon::parse('2026-03-15'))
        ->applies(new SkipCheckParseResult('2026-03-15'));

    expect($applies)->toBeTrue();
});

it('does not apply after the expiry date', function (): void {
    $applies = new SkipCheckEvaluator(Carbon::parse('2026-03-16'))
        ->applies(new SkipCheckParseResult('2026-03-15'));

    expect($applies)->toBeFalse();
});

it('does not apply when skipCheck is absent', function (): void {
    $applies = new SkipCheckEvaluator(Carbon::parse('2026-03-01'))->applies(null);

    expect($applies)->toBeFalse();
});

it('does not apply a bare skip without a date', function (): void {
    $applies = new SkipCheckEvaluator(Carbon::parse('2026-03-01'))
        ->applies(new SkipCheckParseResult(null));

    expect($applies)->toBeFalse();
});

it('does not apply a malformed date', function (): void {
    $applies = new SkipCheckEvaluator(Carbon::parse('2026-03-01'))
        ->applies(new SkipCheckParseResult('2026-13-99'));

    expect($applies)->toBeFalse();
});
