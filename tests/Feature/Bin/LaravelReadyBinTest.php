<?php

declare(strict_types=1);

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Process\Process;

function runLaravelReadyBin(string ...$args): int
{
    $process = new Process(
        array_merge(
            [PHP_BINARY, projectRoot().'/bin/laravel-ready'],
            $args,
        ),
        projectRoot().'/tests/Fixtures/Use',
    );
    $process->run();

    return $process->getExitCode() ?? Command::FAILURE;
}

it('exits success when run without arguments', function (): void {
    expect(runLaravelReadyBin())->toBe(Command::SUCCESS);
});

it('exits failure when project config is missing', function (): void {
    $file = fixture('Legacy/Superglobals/bare.php');

    $process = new Process(
        [PHP_BINARY, projectRoot().'/bin/laravel-ready', $file],
        projectRoot(),
    );
    $process->run();

    expect($process->getExitCode())->toBe(Command::FAILURE)
        ->and($process->getOutput().$process->getErrorOutput())
        ->toContain('Project config not found: laravel-ready.json');
});

it('exits failure when path does not exist', function (): void {
    $file = '/tmp/laravel-ready-missing-'.uniqid('', true).'.php';

    expect(runLaravelReadyBin($file))->toBe(Command::FAILURE);
});

it('returns invalid when path is not a php file', function (): void {
    $file = fixture('not-php.txt');

    expect(runLaravelReadyBin($file))->toBe(Command::INVALID);
});

it('exits failure when run with untagged file', function (): void {
    $file = fixture('Legacy/Superglobals/bare.php');

    expect(runLaravelReadyBin($file))->toBe(Command::FAILURE);
});
