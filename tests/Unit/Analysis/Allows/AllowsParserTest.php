<?php

declare(strict_types=1);

use LaravelReady\Analysis\Allows\AllowsParser;
use LaravelReady\Analysis\Enums\AllowKeyword;
use LaravelReady\Analysis\Enums\BlockedFunction;
use LaravelReady\Analysis\Enums\SuperglobalName;
use LaravelReady\Analysis\Findings\UnknownAllowTokenFinding;

covers(AllowsParser::class);

it('returns null when @allows is absent', function (): void {
    $result = (new AllowsParser)->parseAllows('/** @legacy-adapter */', 1);

    expect($result)->toBeNull();
});

it('parses empty @allows as empty tokens', function (): void {
    $result = (new AllowsParser)->parseAllows("/**\n * @allows\n */", 1);

    expect($result)->not->toBeNull()
        ->and($result->tokens)->toEqual(collect())
        ->and($result->unknowns)->toEqual(collect());
});

it('parses known tokens from a doc comment', function (): void {
    $result = (new AllowsParser)->parseAllows("/**\n * @allows \$_COOKIE, setcookie, global\n */", 1);

    expect($result->tokens)->toEqual(collect([
        SuperglobalName::Cookie,
        BlockedFunction::Setcookie,
        AllowKeyword::Global,
    ]))
        ->and($result->unknowns)->toEqual(collect());
});

it('strips docblock markers from a single-line @allows', function (): void {
    $result = (new AllowsParser)->parseAllows('/** @allows $_GET */', 1);

    expect($result->tokens)->toEqual(collect([
        SuperglobalName::Get,
    ]))
        ->and($result->unknowns)->toEqual(collect());
});

it('collects unknown tokens with line numbers', function (): void {
    $result = (new AllowsParser)->parseAllows("/**\n * @legacy-adapter\n * @allows \$_COOKIE, not-a-thing\n */", 1);

    expect($result->tokens)->toEqual(collect([
        SuperglobalName::Cookie,
    ]))
        ->and($result->unknowns)->toContainEqual(new UnknownAllowTokenFinding('not-a-thing', 3));
});

it('parses a single line of tokens', function (): void {
    $result = (new AllowsParser)->parseLine('$_GET, define', 10);

    expect($result->tokens)->toEqual(collect([
        SuperglobalName::Get,
        BlockedFunction::Define,
    ]));
});
