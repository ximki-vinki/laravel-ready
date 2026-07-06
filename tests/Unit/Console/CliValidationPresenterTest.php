<?php

declare(strict_types=1);

use Illuminate\Filesystem\Filesystem;
use LaravelReady\Console\CliValidationPresenter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\BufferedOutput;

covers(CliValidationPresenter::class);

it('returns success for valid app root', function () {
    $output = new BufferedOutput;

    $exitCode = (new CliValidationPresenter)->presentAppRoot(appRoot(), new Filesystem, $output);

    expect($exitCode)->toBe(Command::SUCCESS)
        ->and($output->fetch())->toBe('');
});

it('returns failure when app root is missing', function () {
    $output = new BufferedOutput;

    $exitCode = (new CliValidationPresenter)->presentAppRoot(null, new Filesystem, $output);

    expect($exitCode)->toBe(Command::FAILURE)
        ->and($output->fetch())->toContain('App root is required. Pass --app-root=/path/to/project/app')
        ->not->toContain('App root not found:');
});

it('returns failure when app root is empty string', function () {
    $output = new BufferedOutput;

    $exitCode = (new CliValidationPresenter)->presentAppRoot('', new Filesystem, $output);

    expect($exitCode)->toBe(Command::FAILURE)
        ->and($output->fetch())->toContain('App root is required. Pass --app-root=/path/to/project/app')
        ->not->toContain('App root not found:');
});

it('returns failure when app root directory is missing', function () {
    $output = new BufferedOutput;
    $missing = '/tmp/laravel-ready-missing-root-'.uniqid();

    $exitCode = (new CliValidationPresenter)->presentAppRoot($missing, new Filesystem, $output);

    expect($exitCode)->toBe(Command::FAILURE)
        ->and($output->fetch())->toContain('App root not found: '.$missing);
});

it('returns success for existing php file path', function () {
    $output = new BufferedOutput;

    $exitCode = (new CliValidationPresenter)->presentPath(fixture('Legacy/Clean/empty.php'), new Filesystem, $output);

    expect($exitCode)->toBe(Command::SUCCESS)
        ->and($output->fetch())->toBe('');
});

it('returns failure when path does not exist', function () {
    $output = new BufferedOutput;
    $missing = '/tmp/laravel-ready-missing-'.uniqid().'.php';

    $exitCode = (new CliValidationPresenter)->presentPath($missing, new Filesystem, $output);

    expect($exitCode)->toBe(Command::FAILURE)
        ->and($output->fetch())->toContain('File not found: '.$missing);
});

it('returns invalid when path is not a php file', function () {
    $output = new BufferedOutput;

    $exitCode = (new CliValidationPresenter)->presentPath(fixture('not-php.txt'), new Filesystem, $output);

    expect($exitCode)->toBe(Command::INVALID)
        ->and($output->fetch())->toContain('Expected a PHP file.');
});
