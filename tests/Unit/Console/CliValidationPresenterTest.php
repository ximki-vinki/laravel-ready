<?php

declare(strict_types=1);

use Illuminate\Filesystem\Filesystem;
use LaravelReady\Console\CliValidationPresenter;
use LaravelReady\Project\NamespaceResolver;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\BufferedOutput;

covers(CliValidationPresenter::class);

it('returns null when project config is missing', function (): void {
    $output = new BufferedOutput;

    $config = new CliValidationPresenter($output)->presentProjectConfigLoad(
        '/tmp/laravel-ready-missing-config-'.uniqid().'.json',
        new Filesystem,
    );

    expect($config)->toBeNull()
        ->and($output->fetch())->toContain('Project config not found: laravel-ready.json');
});

it('returns project config when file is valid', function (): void {
    $output = new BufferedOutput;
    $configPath = fixture('Use/laravel-ready.json');
    $configDir = dirname($configPath);

    $config = new CliValidationPresenter($output)->presentProjectConfigLoad(
        $configPath,
        new Filesystem,
    );

    expect($config)->not->toBeNull()
        ->and($config->resolvers->all())->toEqual([
            new NamespaceResolver('App\\', $configDir.'/project/app'),
        ])
        ->and($output->fetch())->toBe('');
});

it('returns null when project config has an empty prefix', function (): void {
    $output = new BufferedOutput;
    $configPath = projectRoot().'/tests/Fixtures/Config/empty-prefix/laravel-ready.json';

    $config = new CliValidationPresenter($output)->presentProjectConfigLoad(
        $configPath,
        new Filesystem,
    );

    expect($config)->toBeNull()
        ->and($output->fetch())->toContain('Resolver prefix must not be empty.');
});

it('returns null when project config has invalid json', function (): void {
    $output = new BufferedOutput;
    $configPath = projectRoot().'/tests/Fixtures/Config/invalid-json/laravel-ready.json';

    $config = new CliValidationPresenter($output)->presentProjectConfigLoad(
        $configPath,
        new Filesystem,
    );

    expect($config)->toBeNull()
        ->and($output->fetch())->toContain('Invalid laravel-ready.json.');
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
