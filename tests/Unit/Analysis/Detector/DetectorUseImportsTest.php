<?php

declare(strict_types=1);

use LaravelReady\Analysis\Detector;
use LaravelReady\Analysis\UseImportFinding;

covers(Detector::class);

it('collects use imports from ast', function () {
    $result = (new Detector)->analyse(fixture('Use/src/Domain/Invoice.php'));

    expect($result->findings)->toContainEqual(new UseImportFinding('Wf\Legacy\OldRepo', 5));
});
