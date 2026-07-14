<?php

declare(strict_types=1);

use LaravelReady\Analysis\Detector;
use LaravelReady\Analysis\Enums\BlockedFunction;
use LaravelReady\Analysis\Enums\SuperglobalName;
use LaravelReady\Analysis\Enums\Tag;
use LaravelReady\Analysis\Findings\TagFinding;
use LaravelReady\Analysis\Findings\UnknownAllowTokenFinding;

covers(Detector::class);

it('detects allows on legacy-adapter fixture', function (): void {
    $result = (new Detector)->analyse(fixture('Tags/legacy-adapter/with-allows.php'));

    expect($result->findings)->toContainEqual(new TagFinding(Tag::LegacyAdapter, 7))
        ->and($result->allows)->toEqual(collect([
            SuperglobalName::Cookie,
            BlockedFunction::Setcookie,
        ]));
});

it('detects unknown allow tokens as findings', function (): void {
    $result = (new Detector)->analyse(fixture('Tags/legacy-adapter/with-allows-unknown.php'));

    expect($result->allows)->toEqual(collect([
        SuperglobalName::Cookie,
    ]))
        ->and($result->findings)->toContainEqual(new UnknownAllowTokenFinding('not-a-thing', 5));
});

it('detects empty allows', function (): void {
    $result = (new Detector)->analyse(fixture('Tags/legacy-adapter/with-allows-empty.php'));

    expect($result->allows)->toEqual(collect());
});

it('detects no allows without @allows', function (): void {
    $result = (new Detector)->analyse(fixture('Tags/legacy-adapter/class.php'));

    expect($result->allows)->toBeNull();
});
