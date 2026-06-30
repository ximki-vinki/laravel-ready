<?php

declare(strict_types=1);

use LaravelReady\Analysis\BlockedFunction;
use LaravelReady\Analysis\FunctionCallFinding;
use LaravelReady\Analysis\GlobalFinding;
use LaravelReady\Analysis\LegacyDetector;
use LaravelReady\Analysis\SuperglobalFinding;
use LaravelReady\Analysis\SuperglobalName;
use LaravelReady\Analysis\Tag;

covers(LegacyDetector::class);

it('detects legacy define in bare fixture', function () {
    $file = fixture('Legacy/Functions/bare.php');
    $expected = new FunctionCallFinding(BlockedFunction::Define, 3);
    $findings = (new LegacyDetector)->analyse($file)->findings;

    expect($findings)
        ->toHaveCount(1)
        ->toContainEqual($expected);
});

it('detects legacy define in same-line fixture', function () {
    $file = fixture('Legacy/Functions/same-line.php');
    $define = new FunctionCallFinding(BlockedFunction::Define, 3);
    $extract = new FunctionCallFinding(BlockedFunction::Extract, 3);
    $findings = (new LegacyDetector)->analyse($file)->findings;

    expect($findings)
        ->toHaveCount(2)
        ->toContainEqual($define, $extract);
});

it('detects legacy define in all blocked functions fixture', function () {
    $file = fixture('Legacy/Functions/all.php');
    $findings = (new LegacyDetector)->analyse($file)->findings->values()->all();

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
    $legacyFindings = (new LegacyDetector)->analyse($legacy)->findings->values()->all();
    $cleanFindings = (new LegacyDetector)->analyse($clean)->findings;

    expect($legacyFindings)->toEqualCanonicalizing([
        new FunctionCallFinding(BlockedFunction::Assert, 3),
    ])
        ->and($cleanFindings)->toBeEmpty();
});

it('detects legacy parse_str only without result argument', function () {
    $legacy = fixture('Legacy/Functions/parse-str.php');
    $clean = fixture('Legacy/Clean/parse-str.php');
    $legacyFindings = (new LegacyDetector)->analyse($legacy)->findings->values()->all();
    $cleanFindings = (new LegacyDetector)->analyse($clean)->findings;

    expect($legacyFindings)->toEqualCanonicalizing([
        new FunctionCallFinding(BlockedFunction::ParseStr, 3),
    ])
        ->and($cleanFindings)->toBeEmpty();
});

it('detects legacy in blocked function shapes', function (string $fixture, int $line) {
    $file = fixture('Legacy/Functions/'.$fixture);
    $expected = new FunctionCallFinding(BlockedFunction::Define, $line);
    $findings = (new LegacyDetector)->analyse($file)->findings;

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
    $findings = (new LegacyDetector)->analyse($file)->findings->values()->all();

    expect($findings)->toEqualCanonicalizing([
        $bare,
        $assign,
        $inFunction,
    ]);
});

it('detects only legacy patterns in mixed superglobal, blocked function and clean function fixture', function () {
    $file = fixture('Legacy/Mixed/rules.php');
    $findings = (new LegacyDetector)->analyse($file)->findings->values()->all();

    expect($findings)->toEqualCanonicalizing([
        new SuperglobalFinding(SuperglobalName::Get, 3),
        new FunctionCallFinding(BlockedFunction::Define, 4),
    ]);
});

it('detects legacy in bare fixture', function () {
    $file = fixture('Legacy/Superglobals/bare.php');
    $globals = new SuperglobalFinding(SuperglobalName::Globals, 3);
    $cookie = new SuperglobalFinding(SuperglobalName::Cookie, 4);
    $findings = (new LegacyDetector)->analyse($file)->findings;

    expect($findings)
        ->toHaveCount(2)
        ->toContainEqual($globals, $cookie);
});

it('detects legacy in same-line fixture', function () {
    $file = fixture('Legacy/Superglobals/same-line.php');
    $globals = new SuperglobalFinding(SuperglobalName::Globals, 3);
    $cookie = new SuperglobalFinding(SuperglobalName::Cookie, 3);
    $findings = (new LegacyDetector)->analyse($file)->findings;

    expect($findings)
        ->toHaveCount(2)
        ->toContainEqual($globals, $cookie);
});

it('detects legacy in all superglobals fixture', function () {
    $file = fixture('Legacy/Superglobals/all.php');
    $findings = (new LegacyDetector)->analyse($file)->findings->values()->all();

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
    $findings = (new LegacyDetector)->analyse($file)->findings;

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
    $findings = (new LegacyDetector)->analyse($file)->findings->values()->all();

    expect($findings)->toEqualCanonicalizing([
        $globalsBare,
        $cookieAssign,
        $globalsInFunction,
    ]);
});

it('detects legacy global in bare fixture', function () {
    $file = fixture('Legacy/Global/bare.php');
    $expected = new GlobalFinding('foo', 3);
    $findings = (new LegacyDetector)->analyse($file)->findings;

    expect($findings)
        ->toHaveCount(1)
        ->toContainEqual($expected);
});

it('detects legacy global in same-line fixture', function () {
    $file = fixture('Legacy/Global/same-line.php');
    $foo = new GlobalFinding('foo', 3);
    $bar = new GlobalFinding('bar', 3);
    $findings = (new LegacyDetector)->analyse($file)->findings;

    expect($findings)
        ->toHaveCount(2)
        ->toContainEqual($foo, $bar);
});

it('detects legacy in global shapes', function (string $fixture, int $line) {
    $file = fixture('Legacy/Global/'.$fixture);
    $expected = new GlobalFinding('foo', $line);
    $findings = (new LegacyDetector)->analyse($file)->findings;

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
    $findings = (new LegacyDetector)->analyse($file)->findings->values()->all();

    expect($findings)->toEqualCanonicalizing([
        $bare,
        $inFunction,
    ]);
});

it('detects no legacy in clean fixtures', function (string $fixture) {
    $file = fixture('Legacy/Clean/'.$fixture);
    $findings = (new LegacyDetector)->analyse($file)->findings;

    expect($findings)->toBeEmpty();
})->with([
    'comment' => ['comment.php'],
    'string' => ['string.php'],
    'variable' => ['variable.php'],
    'namesake' => ['namesake.php'],
    'parse-str' => ['parse-str.php'],
    'assert' => ['assert.php'],
    'empty' => ['empty.php'],
]);

it('returns no findings when file cannot be read', function () {
    $file = '/tmp/laravel-ready-missing-'.uniqid().'.php';
    $findings = (new LegacyDetector)->analyse($file)->findings;

    expect($findings)->toBeEmpty();
});

it('detects tag on clean fixture', function (Tag $expected, string $path) {
    $result = (new LegacyDetector)->analyse(fixture($path));

    expect($result->tag)->toBe($expected)
        ->and($result->findings)->toBeEmpty();
})->with([
    'legacy-code on class' => [Tag::Legacy, 'Tags/legacy-code/class.php'],
    'legacy-code on function' => [Tag::Legacy, 'Tags/legacy-code/function.php'],
    'legacy-code on method' => [Tag::Legacy, 'Tags/legacy-code/method.php'],
    'legacy-perfect on class' => [Tag::LegacyPerfect, 'Tags/legacy-perfect/class.php'],
]);

it('detects no tag in clean fixtures', function (string $fixture) {
    $result = (new LegacyDetector)->analyse(fixture('Tags/Clean/'.$fixture));

    expect($result->tag)->toBeNull()
        ->and($result->findings)->toBeEmpty();
})->with([
    'empty' => ['empty.php'],
    'no-tag' => ['no-tag.php'],
    'line-comment' => ['line-comment.php'],
    'similar-tag' => ['similar-tag.php'],
]);

it('detects tag alongside blockers in mixed fixture', function () {
    $result = (new LegacyDetector)->analyse(fixture('Tags/Mixed/tag-and-blocker.php'));

    expect($result->tag)->toBe(Tag::Legacy)
        ->and($result->findings)->toContainEqual(new SuperglobalFinding(SuperglobalName::Get, 8));
});
