<?php

declare(strict_types=1);

use LaravelReady\Analysis\GlobalFinding;

covers(GlobalFinding::class);

it('displays global variable name and line number', function (string $variable, int $line, string $expected) {
    $display = new GlobalFinding($variable, $line)->display();

    expect($display)->toBe($expected);
})->with([
    'foo' => ['foo', 3, '$foo (line 3)'],
    'bar' => ['bar', 7, '$bar (line 7)'],
]);
