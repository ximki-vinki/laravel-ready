<?php

declare(strict_types=1);

use LaravelReady\Analysis\Detector;
use LaravelReady\Analysis\Enums\SuperglobalName;
use LaravelReady\Analysis\Findings\SuperglobalFinding;

covers(Detector::class);

it('detects legacy in bare fixture', function () {
    $file = fixture('Legacy/Superglobals/bare.php');
    $globals = new SuperglobalFinding(SuperglobalName::Globals, 3);
    $cookie = new SuperglobalFinding(SuperglobalName::Cookie, 4);
    $findings = (new Detector)->analyse($file)->findings;

    expect($findings)
        ->toHaveCount(2)
        ->toContainEqual($globals, $cookie);
});

it('detects legacy in same-line fixture', function () {
    $file = fixture('Legacy/Superglobals/same-line.php');
    $globals = new SuperglobalFinding(SuperglobalName::Globals, 3);
    $cookie = new SuperglobalFinding(SuperglobalName::Cookie, 3);
    $findings = (new Detector)->analyse($file)->findings;

    expect($findings)
        ->toHaveCount(2)
        ->toContainEqual($globals, $cookie);
});

it('detects legacy in all superglobals fixture', function () {
    $file = fixture('Legacy/Superglobals/all.php');
    $findings = (new Detector)->analyse($file)->findings->values()->all();

    expect($findings)->toEqualCanonicalizing([
        new SuperglobalFinding(SuperglobalName::Globals, 3),
        new SuperglobalFinding(SuperglobalName::Server, 4),
        new SuperglobalFinding(SuperglobalName::Get, 5),
        new SuperglobalFinding(SuperglobalName::Post, 6),
        new SuperglobalFinding(SuperglobalName::Files, 7),
        new SuperglobalFinding(SuperglobalName::Cookie, 8),
        new SuperglobalFinding(SuperglobalName::Session, 9),
        new SuperglobalFinding(SuperglobalName::Request, 10),
        new SuperglobalFinding(SuperglobalName::Env, 11),
    ]);
});

it('detects legacy in superglobal shapes', function (string $fixture, int $line) {
    $file = fixture('Legacy/Superglobals/'.$fixture);
    $expected = new SuperglobalFinding(SuperglobalName::Globals, $line);
    $findings = (new Detector)->analyse($file)->findings;

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
    'in-class' => ['in-class.php', 7],
    'closure' => ['closure.php', 3],
    'foreach' => ['foreach.php', 3],
]);

it('detects legacy in mixed fixture', function () {
    $file = fixture('Legacy/Superglobals/mixed.php');
    $globalsBare = new SuperglobalFinding(SuperglobalName::Globals, 3);
    $cookieAssign = new SuperglobalFinding(SuperglobalName::Cookie, 4);
    $globalsInFunction = new SuperglobalFinding(SuperglobalName::Globals, 8);
    $findings = (new Detector)->analyse($file)->findings->values()->all();

    expect($findings)->toEqualCanonicalizing([
        $globalsBare,
        $cookieAssign,
        $globalsInFunction,
    ]);
});
