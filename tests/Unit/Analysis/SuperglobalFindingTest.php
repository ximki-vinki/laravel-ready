<?php

declare(strict_types=1);

use LaravelReady\Analysis\SuperglobalFinding;
use LaravelReady\Analysis\SuperglobalName;

covers(SuperglobalFinding::class);

it('displays superglobal name and line number', function (SuperglobalName $name, int $line, string $expected) {
    $display = new SuperglobalFinding($name, $line)->display();

    expect($display)->toBe($expected);
})->with([
    'globals' => [SuperglobalName::Globals, 3, '$GLOBALS (line 3)'],
    'cookie' => [SuperglobalName::Cookie, 4, '$_COOKIE (line 4)'],
]);
