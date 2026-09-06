<?php

declare(strict_types=1);

use LaravelReady\Analysis\SkipCheck\SkipCheckParser;

covers(SkipCheckParser::class);

it('returns null when @skipCheck is absent', function (): void {
    $result = (new SkipCheckParser)->parse('/** @laravel-adapter */');

    expect($result)->toBeNull();
});

it('parses a bare @skipCheck as a missing date', function (): void {
    $result = (new SkipCheckParser)->parse('/** @laravel-adapter @skipCheck */');

    expect($result)->not->toBeNull()
        ->and($result->date)->toBeNull();
});

it('parses an absolute date from @skipCheck', function (): void {
    $result = (new SkipCheckParser)->parse('/** @skipCheck(2026-03-15) */');

    expect($result->date)->toBe('2026-03-15');
});

it('trims spaces inside the date argument', function (): void {
    $result = (new SkipCheckParser)->parse('/** @skipCheck( 2026-03-15 ) */');

    expect($result->date)->toBe('2026-03-15');
});

it('does not treat @skipChecked as @skipCheck', function (): void {
    $result = (new SkipCheckParser)->parse('/** @skipChecked(2026-03-15) */');

    expect($result)->toBeNull();
});

it('parses @skipCheck from a later line of a multiline docblock', function (): void {
    $result = (new SkipCheckParser)->parse("/**\n * @laravel-adapter\n * @skipCheck(2026-03-15)\n */");

    expect($result->date)->toBe('2026-03-15');
});
