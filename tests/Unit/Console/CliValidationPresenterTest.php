<?php

declare(strict_types=1);

use Illuminate\Filesystem\Filesystem;
use LaravelReady\Console\CliValidationPresenter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\BufferedOutput;

covers(CliValidationPresenter::class);

it('returns success for valid app root', function (): void {
    $output = new BufferedOutput;

    $exitCode = new CliValidationPresenter($output)->presentAppRoot(appRoot(), new Filesystem);

    expect($exitCode)->toBe(Command::SUCCESS)
        ->and($output->fetch())->toBe('');
});

it('returns failure when app root is missing', function (): void {
    $output = new BufferedOutput;

    $exitCode = new CliValidationPresenter($output)->presentAppRoot(null, new Filesystem);

    expect($exitCode)->toBe(Command::FAILURE)
        ->and($output->fetch())->toContain('App root is required. Pass --app-root=/path/to/project/app')
        ->not->toContain('App root not found:');
});

it('returns failure when app root is empty string', function (): void {
    $output = new BufferedOutput;

    $exitCode = new CliValidationPresenter($output)->presentAppRoot('', new Filesystem);

    expect($exitCode)->toBe(Command::FAILURE)
        ->and($output->fetch())->toContain('App root is required. Pass --app-root=/path/to/project/app')
        ->not->toContain('App root not found:');
});

it('returns failure when app root directory is missing', function (): void {
    $output = new BufferedOutput;
    $missing = '/tmp/laravel-ready-missing-root-'.uniqid();

    $exitCode = new CliValidationPresenter($output)->presentAppRoot($missing, new Filesystem);

    expect($exitCode)->toBe(Command::FAILURE)
        ->and($output->fetch())->toContain('App root not found: '.$missing);
});

it('returns success for existing php file path', function (): void {
    $output = new BufferedOutput;

    $exitCode = new CliValidationPresenter($output)->presentPath(fixture('Legacy/Clean/empty.php'), new Filesystem);

    expect($exitCode)->toBe(Command::SUCCESS)
        ->and($output->fetch())->toBe('');
});

it('returns failure when path does not exist', function (): void {
    $output = new BufferedOutput;
    $missing = '/tmp/laravel-ready-missing-'.uniqid().'.php';

    $exitCode = new CliValidationPresenter($output)->presentPath($missing, new Filesystem);

    expect($exitCode)->toBe(Command::FAILURE)
        ->and($output->fetch())->toContain('File not found: '.$missing);
});

it('returns invalid when path is not a php file', function (): void {
    $output = new BufferedOutput;

    $exitCode = new CliValidationPresenter($output)->presentPath(fixture('not-php.txt'), new Filesystem);

    expect($exitCode)->toBe(Command::INVALID)
        ->and($output->fetch())->toContain('Expected a PHP file.');
});

it('returns failure when no php files were resolved', function (): void {
    $output = new BufferedOutput;

    $exitCode = new CliValidationPresenter($output)->presentPhpFilesFound(false);

    expect($exitCode)->toBe(Command::FAILURE)
        ->and($output->fetch())->toContain('PHP files not found');
});

it('returns success when php files were resolved', function (): void {
    $output = new BufferedOutput;

    $exitCode = new CliValidationPresenter($output)->presentPhpFilesFound(true);

    expect($exitCode)->toBe(Command::SUCCESS)
        ->and($output->fetch())->toBe('');
});
