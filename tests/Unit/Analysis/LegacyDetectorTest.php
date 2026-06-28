<?php

declare(strict_types=1);

use LaravelReady\Analysis\LegacyDetector;
use LaravelReady\Analysis\SuperglobalFinding;
use LaravelReady\Analysis\SuperglobalName;

covers(LegacyDetector::class);

it('detects legacy in bare fixture', function () {
    $file = fixture('Legacy/Superglobals/bare.php');
    $globals = new SuperglobalFinding(SuperglobalName::Globals, 3);
    $cookie = new SuperglobalFinding(SuperglobalName::Cookie, 4);

    $findings = (new LegacyDetector)->analyse($file);

    expect($findings)
        ->toHaveCount(2)
        ->toContainEqual($globals, $cookie);
});

it('detects legacy in superglobal shapes', function (string $fixture, int $line) {
    $file = fixture('Legacy/Superglobals/'.$fixture);
    $expected = new SuperglobalFinding(SuperglobalName::Globals, $line);

    $findings = (new LegacyDetector)->analyse($file);

    expect($findings)
        ->toHaveCount(1)
        ->toContainEqual($expected);
})->with([
    'assign' => ['assign.php', 3],
    'array-access' => ['array-access.php', 3],
    'as-argument' => ['as-argument.php', 3],
    'isset' => ['isset.php', 3],
    'in-function' => ['in-function.php', 5],
    'return' => ['return.php', 5],
]);

it('detects legacy in mixed fixture', function () {
    $file = fixture('Legacy/Superglobals/mixed.php');
    $globalsBare = new SuperglobalFinding(SuperglobalName::Globals, 3);
    $cookieAssign = new SuperglobalFinding(SuperglobalName::Cookie, 4);
    $globalsInFunction = new SuperglobalFinding(SuperglobalName::Globals, 8);

    $findings = (new LegacyDetector)->analyse($file);

    expect($findings->values()->all())->toEqualCanonicalizing([
        $globalsBare,
        $cookieAssign,
        $globalsInFunction,
    ]);
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
