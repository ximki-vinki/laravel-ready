<?php

declare(strict_types=1);

use Illuminate\Support\Carbon;
use LaravelReady\Analysis\SkipCheck\SkipCheckEvaluator;
use LaravelReady\Analysis\SkipCheck\SkipCheckParseResult;
use LaravelReady\Analysis\SkipCheck\SkipCheckVerdict;

covers(SkipCheckEvaluator::class);

it('returns active for a future absolute date', function (): void {
    $verdict = new SkipCheckEvaluator(Carbon::parse('2026-03-01'))
        ->verdict(new SkipCheckParseResult('2026-03-15'));

    expect($verdict)->toBe(SkipCheckVerdict::Active);
});

it('returns active on the expiry date itself', function (): void {
    $verdict = new SkipCheckEvaluator(Carbon::parse('2026-03-15'))
        ->verdict(new SkipCheckParseResult('2026-03-15'));

    expect($verdict)->toBe(SkipCheckVerdict::Active);
});

it('returns expired after the expiry date', function (): void {
    $verdict = new SkipCheckEvaluator(Carbon::parse('2026-03-16'))
        ->verdict(new SkipCheckParseResult('2026-03-15'));

    expect($verdict)->toBe(SkipCheckVerdict::Expired);
});

it('returns absent when skipCheck is missing', function (): void {
    $verdict = new SkipCheckEvaluator(Carbon::parse('2026-03-01'))->verdict(null);

    expect($verdict)->toBe(SkipCheckVerdict::Absent);
});

it('returns bare when skipCheck has no date', function (): void {
    $verdict = new SkipCheckEvaluator(Carbon::parse('2026-03-01'))
        ->verdict(new SkipCheckParseResult(null));

    expect($verdict)->toBe(SkipCheckVerdict::Bare);
});

it('returns malformed for an invalid date', function (): void {
    $verdict = new SkipCheckEvaluator(Carbon::parse('2026-03-01'))
        ->verdict(new SkipCheckParseResult('2026-13-99'));

    expect($verdict)->toBe(SkipCheckVerdict::Malformed);
});

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
