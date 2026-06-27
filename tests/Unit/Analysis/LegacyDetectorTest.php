<?php

declare(strict_types=1);

use LaravelReady\Analysis\LegacyDetector;
use LaravelReady\Analysis\SuperglobalFinding;
use LaravelReady\Analysis\SuperglobalName;

covers(LegacyDetector::class);

it('detects legacy in globals fixture', function () {
    $file = fixture('Legacy/globals.php');

    $findings = (new LegacyDetector)->analyse($file);

    expect($findings)->toHaveCount(1)
        ->and($findings->first())->toEqual(new SuperglobalFinding(SuperglobalName::Globals, 3));
});

it('detects no findings in empty fixture', function () {
    $file = fixture('Legacy/empty.php');

    $findings = (new LegacyDetector)->analyse($file);

    expect($findings)->toBeEmpty();
});

it('returns no findings when file cannot be read', function () {
    $file = '/tmp/laravel-ready-missing-'.uniqid().'.php';

    $findings = (new LegacyDetector)->analyse($file);

    expect($findings)->toBeEmpty();
});
