<?php

declare(strict_types=1);

use LaravelReady\Analysis\Allows\AllowListParser;
use LaravelReady\Analysis\Enums\AllowKeyword;
use LaravelReady\Analysis\Enums\BlockedFunction;
use LaravelReady\Analysis\Enums\SuperglobalName;
use LaravelReady\Analysis\Findings\UnknownAllowTokenFinding;

covers(AllowListParser::class);

it('returns null when @allows is absent', function (): void {
    $result = (new AllowListParser)->parseDocComment('/** @legacy-adapter */', 1);

    expect($result)->toBeNull();
});

it('parses empty @allows as empty tokens', function (): void {
    $result = (new AllowListParser)->parseDocComment("/**\n * @allows\n */", 1);

    expect($result)->not->toBeNull()
        ->and($result->tokens)->toEqual(collect())
        ->and($result->unknowns)->toEqual(collect());
});

it('parses known tokens from a doc comment', function (): void {
    $doc = <<<'DOC'
/**
 * @allows $_COOKIE, setcookie, global
 */
DOC;

    $result = (new AllowListParser)->parseDocComment($doc, 1);

    expect($result->tokens)->toEqual(collect([
        SuperglobalName::Cookie,
        BlockedFunction::Setcookie,
        AllowKeyword::Global,
    ]))
        ->and($result->unknowns)->toEqual(collect());
});

it('collects unknown tokens with line numbers', function (): void {
    $doc = <<<'DOC'
/**
 * @legacy-adapter
 * @allows $_COOKIE, not-a-thing
 */
DOC;

    $result = (new AllowListParser)->parseDocComment($doc, 1);

    expect($result->tokens)->toEqual(collect([
        SuperglobalName::Cookie,
    ]))
        ->and($result->unknowns)->toContainEqual(new UnknownAllowTokenFinding('not-a-thing', 3));
});

it('parses a single line of tokens', function (): void {
    $result = (new AllowListParser)->parseLine('$_GET, define', 10);

    expect($result->tokens)->toEqual(collect([
        SuperglobalName::Get,
        BlockedFunction::Define,
    ]));
});
