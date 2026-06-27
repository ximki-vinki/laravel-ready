<?php

declare(strict_types=1);

use LaravelReady\Analysis\LegacyDetector;

it('detects legacy in globals fixture', function () {
    $file = fixture('Legacy/globals.php');

    $result = (new LegacyDetector)->isLegacy($file);
    expect($result)->toBe('GLOBALS');
});

it('detects non-legacy in empty fixture', function () {
    $file = fixture('Legacy/empty.php');

    $result = (new LegacyDetector)->isLegacy($file);
    expect($result)->toBeNull();
});
