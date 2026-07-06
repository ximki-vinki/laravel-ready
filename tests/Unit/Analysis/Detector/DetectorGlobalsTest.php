<?php

declare(strict_types=1);

use LaravelReady\Analysis\Detector;
use LaravelReady\Analysis\Findings\GlobalFinding;

covers(Detector::class);

it('detects legacy global in bare fixture', function () {
    $file = fixture('Legacy/Global/bare.php');
    $expected = new GlobalFinding('foo', 3);
    $findings = (new Detector)->analyse($file)->findings;

    expect($findings)
        ->toHaveCount(1)
        ->toContainEqual($expected);
});

it('detects legacy global in same-line fixture', function () {
    $file = fixture('Legacy/Global/same-line.php');
    $foo = new GlobalFinding('foo', 3);
    $bar = new GlobalFinding('bar', 3);
    $findings = (new Detector)->analyse($file)->findings;

    expect($findings)
        ->toHaveCount(2)
        ->toContainEqual($foo, $bar);
});

it('detects legacy in global shapes', function (string $fixture, int $line) {
    $file = fixture('Legacy/Global/'.$fixture);
    $expected = new GlobalFinding('foo', $line);
    $findings = (new Detector)->analyse($file)->findings;

    expect($findings)
        ->toHaveCount(1)
        ->toContainEqual($expected);
})->with([
    'in-function' => ['in-function.php', 5],
    'in-class' => ['in-class.php', 7],
]);

it('detects legacy in global mixed fixture', function () {
    $file = fixture('Legacy/Global/mixed.php');
    $bare = new GlobalFinding('foo', 3);
    $inFunction = new GlobalFinding('baz', 8);
    $findings = (new Detector)->analyse($file)->findings->values()->all();

    expect($findings)->toEqualCanonicalizing([
        $bare,
        $inFunction,
    ]);
});
