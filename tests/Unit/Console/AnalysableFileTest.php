<?php

declare(strict_types=1);

use LaravelReady\Console\AnalysableFile;
use Symfony\Component\Finder\SplFileInfo;

covers(AnalysableFile::class);

it('maps explicit file path to basename relative path', function () {
    $path = fixture('Legacy/Superglobals/bare.php');

    $file = AnalysableFile::fromExplicitFile($path);

    expect($file->absolutePath)->toBe($path)
        ->and($file->relativePath)->toBe('bare.php');
});

it('maps directory entry to relative path within directory', function () {
    $entry = new SplFileInfo(
        fixture('Legacy/Superglobals/bare.php'),
        'Superglobals',
        'Superglobals/bare.php',
    );

    $file = AnalysableFile::fromDirectoryEntry($entry);

    expect($file->absolutePath)->toBe(fixture('Legacy/Superglobals/bare.php'))
        ->and($file->relativePath)->toBe('Superglobals/bare.php');
});
