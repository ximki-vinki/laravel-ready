<?php

declare(strict_types=1);

use LaravelReady\Analysis\Detector;

covers(Detector::class);

it('detects no legacy in clean fixtures', function (string $fixture): void {
    $file = fixture('Legacy/Clean/'.$fixture);
    $findings = (new Detector)->analyse($file)->findings;

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
