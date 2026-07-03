<?php

declare(strict_types=1);

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Process\Process;

function runLaravelReadyBin(string ...$args): int
{
    $process = new Process(
        array_merge([PHP_BINARY, 'bin/laravel-ready'], $args),
        dirname(__DIR__, 3),
    );
    $process->run();

    return $process->getExitCode() ?? Command::FAILURE;
}

it('exits success when run without arguments', function () {
    expect(runLaravelReadyBin())->toBe(Command::SUCCESS);
});

it('exits failure when path does not exist', function () {
    $file = '/tmp/laravel-ready-missing-'.uniqid('', true).'.php';

    expect(runLaravelReadyBin($file))->toBe(Command::FAILURE);
});

it('returns invalid when path is not a php file', function () {
    $file = fixture('not-php.txt');

    expect(runLaravelReadyBin($file))->toBe(Command::INVALID);
});

it('exits failure when run with untagged file', function () {
    $file = fixture('Legacy/Superglobals/bare.php');

    expect(runLaravelReadyBin($file))->toBe(Command::FAILURE);
});
