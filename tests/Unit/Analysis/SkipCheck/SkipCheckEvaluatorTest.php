<?php

declare(strict_types=1);

use Illuminate\Support\Carbon;
use LaravelReady\Analysis\SkipCheck\SkipCheckEvaluator;

covers(SkipCheckEvaluator::class);

it('applies a future absolute date', function (): void {
    $applies = (new SkipCheckEvaluator)->applies('2026-03-15', Carbon::parse('2026-03-01'));

    expect($applies)->toBeTrue();
});

it('applies on the expiry date itself', function (): void {
    $applies = (new SkipCheckEvaluator)->applies('2026-03-15', Carbon::parse('2026-03-15'));

    expect($applies)->toBeTrue();
});

it('does not apply after the expiry date', function (): void {
    $applies = (new SkipCheckEvaluator)->applies('2026-03-15', Carbon::parse('2026-03-16'));

    expect($applies)->toBeFalse();
});

it('does not apply a bare skip without a date', function (): void {
    $applies = (new SkipCheckEvaluator)->applies(null, Carbon::parse('2026-03-01'));

    expect($applies)->toBeFalse();
});

it('does not apply a malformed date', function (): void {
    $applies = (new SkipCheckEvaluator)->applies('2026-13-99', Carbon::parse('2026-03-01'));

    expect($applies)->toBeFalse();
});
