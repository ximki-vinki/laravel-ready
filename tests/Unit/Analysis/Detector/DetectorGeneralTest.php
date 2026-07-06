<?php

declare(strict_types=1);

use LaravelReady\Analysis\BlockedFunction;
use LaravelReady\Analysis\Detector;
use LaravelReady\Analysis\FunctionCallFinding;
use LaravelReady\Analysis\SuperglobalFinding;
use LaravelReady\Analysis\SuperglobalName;

covers(Detector::class);

it('detects only legacy patterns in mixed superglobal, blocked function and clean function fixture', function () {
    $file = fixture('Legacy/Mixed/rules.php');
    $findings = (new Detector)->analyse($file)->findings->values()->all();

    expect($findings)->toEqualCanonicalizing([
        new SuperglobalFinding(SuperglobalName::Get, 3),
        new FunctionCallFinding(BlockedFunction::Define, 4),
    ]);
});

it('returns no findings when file cannot be read', function () {
    $file = '/tmp/laravel-ready-missing-'.uniqid().'.php';
    $findings = (new Detector)->analyse($file)->findings;

    expect($findings)->toBeEmpty();
});
