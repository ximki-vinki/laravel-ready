<?php

declare(strict_types=1);

use LaravelReady\Analysis\LegacyDetector;
use LaravelReady\Analysis\SuperglobalFinding;
use LaravelReady\Analysis\SuperglobalName;

covers(LegacyDetector::class);

it('detects legacy in globals fixture', function () {
    $file = fixture('Legacy/globals.php');
    $globals = new SuperglobalFinding(SuperglobalName::Globals, 3);
    $cookie = new SuperglobalFinding(SuperglobalName::Cookie, 4);

    $findings = (new LegacyDetector)->analyse($file);

    expect($findings)
        ->toHaveCount(2)
        ->toContainEqual($globals, $cookie);
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
