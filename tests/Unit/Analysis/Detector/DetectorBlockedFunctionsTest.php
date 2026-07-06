<?php

declare(strict_types=1);

use LaravelReady\Analysis\Detector;
use LaravelReady\Analysis\Enums\BlockedFunction;
use LaravelReady\Analysis\Findings\FunctionCallFinding;

covers(Detector::class);

it('detects legacy define in bare fixture', function () {
    $file = fixture('Legacy/Functions/bare.php');
    $expected = new FunctionCallFinding(BlockedFunction::Define, 3);
    $findings = (new Detector)->analyse($file)->findings;

    expect($findings)
        ->toHaveCount(1)
        ->toContainEqual($expected);
});

it('detects legacy define in same-line fixture', function () {
    $file = fixture('Legacy/Functions/same-line.php');
    $define = new FunctionCallFinding(BlockedFunction::Define, 3);
    $extract = new FunctionCallFinding(BlockedFunction::Extract, 3);
    $findings = (new Detector)->analyse($file)->findings;

    expect($findings)
        ->toHaveCount(2)
        ->toContainEqual($define, $extract);
});

it('detects legacy define in all blocked functions fixture', function () {
    $file = fixture('Legacy/Functions/all.php');
    $findings = (new Detector)->analyse($file)->findings->values()->all();

    expect($findings)->toEqualCanonicalizing([
        new FunctionCallFinding(BlockedFunction::Define, 3),
        new FunctionCallFinding(BlockedFunction::Extract, 4),
        new FunctionCallFinding(BlockedFunction::Compact, 5),
        new FunctionCallFinding(BlockedFunction::Eval, 6),
        new FunctionCallFinding(BlockedFunction::Utf8Encode, 7),
        new FunctionCallFinding(BlockedFunction::Utf8Decode, 8),
        new FunctionCallFinding(BlockedFunction::ParseStr, 9),
        new FunctionCallFinding(BlockedFunction::SessionStart, 10),
        new FunctionCallFinding(BlockedFunction::Setcookie, 11),
        new FunctionCallFinding(BlockedFunction::Header, 12),
        new FunctionCallFinding(BlockedFunction::Mail, 13),
        new FunctionCallFinding(BlockedFunction::Strftime, 14),
        new FunctionCallFinding(BlockedFunction::Putenv, 15),
        new FunctionCallFinding(BlockedFunction::Getenv, 16),
        new FunctionCallFinding(BlockedFunction::Assert, 17),
    ]);
});

it('detects legacy assert only with string argument', function () {
    $legacy = fixture('Legacy/Functions/assert.php');
    $clean = fixture('Legacy/Clean/assert.php');
    $legacyFindings = (new Detector)->analyse($legacy)->findings->values()->all();
    $cleanFindings = (new Detector)->analyse($clean)->findings;

    expect($legacyFindings)->toEqualCanonicalizing([
        new FunctionCallFinding(BlockedFunction::Assert, 3),
    ])
        ->and($cleanFindings)->toBeEmpty();
});

it('detects legacy parse_str only without result argument', function () {
    $legacy = fixture('Legacy/Functions/parse-str.php');
    $clean = fixture('Legacy/Clean/parse-str.php');
    $legacyFindings = (new Detector)->analyse($legacy)->findings->values()->all();
    $cleanFindings = (new Detector)->analyse($clean)->findings;

    expect($legacyFindings)->toEqualCanonicalizing([
        new FunctionCallFinding(BlockedFunction::ParseStr, 3),
    ])
        ->and($cleanFindings)->toBeEmpty();
});

it('detects legacy in blocked function shapes', function (string $fixture, int $line) {
    $file = fixture('Legacy/Functions/'.$fixture);
    $expected = new FunctionCallFinding(BlockedFunction::Define, $line);
    $findings = (new Detector)->analyse($file)->findings;

    expect($findings)
        ->toHaveCount(1)
        ->toContainEqual($expected);
})->with([
    'assign' => ['assign.php', 3],
    'as-argument' => ['as-argument.php', 3],
    'condition' => ['condition.php', 3],
    'namespaced-call' => ['namespaced-call.php', 3],
    'in-function' => ['in-function.php', 5],
    'return' => ['return.php', 5],
    'in-class' => ['in-class.php', 7],
    'closure' => ['closure.php', 3],
]);

it('detects legacy in functions mixed fixture', function () {
    $file = fixture('Legacy/Functions/mixed.php');
    $bare = new FunctionCallFinding(BlockedFunction::Define, 3);
    $assign = new FunctionCallFinding(BlockedFunction::Define, 4);
    $inFunction = new FunctionCallFinding(BlockedFunction::Define, 8);
    $findings = (new Detector)->analyse($file)->findings->values()->all();

    expect($findings)->toEqualCanonicalizing([
        $bare,
        $assign,
        $inFunction,
    ]);
});
