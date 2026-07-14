<?php

declare(strict_types=1);

use LaravelReady\Analysis\Detector;
use LaravelReady\Analysis\Enums\SuperglobalName;
use LaravelReady\Analysis\Enums\Tag;
use LaravelReady\Analysis\Findings\SuperglobalFinding;
use LaravelReady\Analysis\Findings\TagFinding;

covers(Detector::class);

it('detects skipCheck on laravel-adapter fixture', function (): void {
    $result = (new Detector)->analyse(fixture('Tags/laravel-adapter/skip-check.php'));

    expect($result->skipCheck)->toBeTrue()
        ->and($result->findings)->toContainEqual(new TagFinding(Tag::LaravelAdapter, 4));
});

it('detects skipCheck alongside blockers', function (): void {
    $result = (new Detector)->analyse(fixture('Tags/laravel-adapter/skip-check-with-blocker.php'));

    expect($result->skipCheck)->toBeTrue()
        ->and($result->findings)->toContainEqual(new TagFinding(Tag::LaravelAdapter, 5))
        ->and($result->findings)->toContainEqual(new SuperglobalFinding(SuperglobalName::Get, 5));
});

it('detects no skipCheck without @skipCheck', function (): void {
    $result = (new Detector)->analyse(fixture('Tags/laravel-adapter/class.php'));

    expect($result->skipCheck)->toBeFalse();
});
