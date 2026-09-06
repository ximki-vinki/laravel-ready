<?php

declare(strict_types=1);

use LaravelReady\Analysis\Allows\UnknownAllowToken;
use LaravelReady\Analysis\Detector;
use LaravelReady\Analysis\Enums\AllowKeyword;
use LaravelReady\Analysis\Enums\BlockedFunction;
use LaravelReady\Analysis\Enums\SuperglobalName;
use LaravelReady\Analysis\Enums\Tag;
use LaravelReady\Analysis\Findings\TagFinding;

covers(Detector::class);

it('detects allows on legacy-adapter fixture', function (): void {
    $result = (new Detector)->analyse(fixture('Tags/legacy-adapter/with-allows.php'));
    $allows = $result->modifiers->allows;

    expect($result->findings)->toContainEqual(new TagFinding(Tag::LegacyAdapter, 7))
        ->and($allows)->not->toBeNull()
        ->and($allows->tokens)->toEqual(collect([
            SuperglobalName::Cookie,
            BlockedFunction::Setcookie,
        ]));
});

it('detects global allow token on legacy-adapter fixture', function (): void {
    $allows = (new Detector)->analyse(fixture('Tags/legacy-adapter/with-allows-global.php'))->modifiers->allows;

    expect($allows)->not->toBeNull()
        ->and($allows->tokens)->toEqual(collect([
            SuperglobalName::Cookie,
            BlockedFunction::Setcookie,
            AllowKeyword::Global,
        ]));
});

it('keeps unknown allow tokens on the allows modifier', function (): void {
    $result = (new Detector)->analyse(fixture('Tags/legacy-adapter/with-allows-unknown.php'));
    $allows = $result->modifiers->allows;

    expect($allows)->not->toBeNull()
        ->and($allows->tokens)->toEqual(collect([
            SuperglobalName::Cookie,
        ]))
        ->and($allows->unknowns)->toContainEqual(new UnknownAllowToken('not-a-thing', 5))
        ->and($result->findings)->not->toContainEqual(new UnknownAllowToken('not-a-thing', 5));
});

it('detects empty allows', function (): void {
    $allows = (new Detector)->analyse(fixture('Tags/legacy-adapter/with-allows-empty.php'))->modifiers->allows;

    expect($allows)->not->toBeNull()
        ->and($allows->tokens)->toEqual(collect())
        ->and($allows->unknowns)->toEqual(collect());
});

it('detects no allows without @allows', function (): void {
    $result = (new Detector)->analyse(fixture('Tags/legacy-adapter/class.php'));

    expect($result->modifiers->allows)->toBeNull();
});

it('ignores a second @allows on a later node', function (): void {
    $allows = (new Detector)->analyse(fixture('Tags/legacy-adapter/with-allows-second-ignored.php'))->modifiers->allows;

    expect($allows)->not->toBeNull()
        ->and($allows->tokens)->toEqual(collect([
            SuperglobalName::Cookie,
        ]))
        ->and($allows->unknowns)->not->toContainEqual(
            new UnknownAllowToken('not-from-second', 10),
        );
});
