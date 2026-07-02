<?php

declare(strict_types=1);

use LaravelReady\Console\Commands\AnalyseCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

covers(AnalyseCommand::class);

it('returns success when run without path', function () {
    $tester = new CommandTester(new AnalyseCommand);

    $code = $tester->execute([]);
    // Можно протестировать только через Arguments
    expect($code)->toBe(Command::SUCCESS)
        ->and($tester->getDisplay())->toContain('Arguments:');
});

it('fails when path does not exist', function () {
    $tester = new CommandTester(new AnalyseCommand);
    $code = $tester->execute(['path' => ['/tmp/laravel-ready-missing-'.uniqid().'.php']]);
    expect($code)->toBe(Command::FAILURE)
        ->and($tester->getDisplay())->toContain('File not found');
});

it('returns invalid when path is not a php file', function () {
    $tester = new CommandTester(new AnalyseCommand);

    $code = $tester->execute(['path' => [fixture('not-php.txt')]]);

    expect($code)->toBe(Command::INVALID)
        ->and($tester->getDisplay())->toContain('Expected a PHP file');
});

it('returns success for laravel-ready fixture without blockers', function () {
    $tester = new CommandTester(new AnalyseCommand);

    $code = $tester->execute(['path' => [fixture('Tags/laravel-ready/class.php')]]);

    expect($code)->toBe(Command::SUCCESS)
        ->and($tester->getDisplay())->toContain('class.php')
        ->and($tester->getDisplay())->toContain('LaravelReady');
});

it('analyses php files in subdirectories', function () {
    $tester = new CommandTester(new AnalyseCommand);

    $code = $tester->execute(['path' => [fixture('Legacy')]]);

    expect($code)->toBe(Command::SUCCESS)
        ->and($tester->getDisplay())->toContain('Superglobals/bare.php')
        ->and($tester->getDisplay())->toContain('Clean/empty.php');
});

it('analyses directory path', function () {
    $tester = new CommandTester(new AnalyseCommand);

    $code = $tester->execute(['path' => [fixture('Legacy/Mixed')]]);

    expect($code)->toBe(Command::SUCCESS)
        ->and($tester->getDisplay())->toContain('rules.php')
        ->and($tester->getDisplay())->toContain('Legacy')
        ->and($tester->getDisplay())->toContain('$_GET (line 3)')
        ->and($tester->getDisplay())->toContain('define() (line 4)');
});

it('prints legacy level and findings for mixed fixture', function () {
    $tester = new CommandTester(new AnalyseCommand);

    $code = $tester->execute(['path' => [fixture('Legacy/Mixed/rules.php')]]);

    expect($code)->toBe(Command::SUCCESS)
        ->and($tester->getDisplay())->toContain('rules.php')
        ->and($tester->getDisplay())->toContain('Legacy')
        ->and($tester->getDisplay())->toContain('$_GET (line 3)')
        ->and($tester->getDisplay())->toContain('define() (line 4)');
});

it('prints laravel ready level for clean fixture', function () {
    $tester = new CommandTester(new AnalyseCommand);

    $tester->execute(['path' => [fixture('Legacy/Clean/empty.php')]]);

    expect($tester->getDisplay())->toContain('empty.php')
        ->and($tester->getDisplay())->toContain('LaravelReady')
        ->and($tester->getDisplay())->not->toContain('$GLOBALS');
});

it('analyses multiple file paths passed as separate arguments', function () {
    $tester = new CommandTester(new AnalyseCommand);

    $code = $tester->execute([
        'path' => [
            fixture('Legacy/Superglobals/bare.php'),
            fixture('Legacy/Clean/empty.php'),
        ],
    ]);

    expect($code)->toBe(Command::SUCCESS)
        ->and($tester->getDisplay())->toContain('bare.php')
        ->and($tester->getDisplay())->toContain('empty.php')
        ->and($tester->getDisplay())->toContain('LaravelReady');
});

it('analyses multiple directory paths', function () {
    $tester = new CommandTester(new AnalyseCommand);

    $code = $tester->execute([
        'path' => [
            fixture('Legacy/Superglobals'),
            fixture('Legacy/Clean'),
        ],
    ]);

    expect($code)->toBe(Command::SUCCESS)
        ->and($tester->getDisplay())->toContain('bare.php')
        ->and($tester->getDisplay())->toContain('empty.php')
        ->and($tester->getDisplay())->toContain('LaravelReady');
});

it('analyses directory and file path together', function () {
    $tester = new CommandTester(new AnalyseCommand);

    $code = $tester->execute([
        'path' => [
            fixture('Legacy/Superglobals'),
            fixture('Legacy/Clean/empty.php'),
        ],
    ]);

    expect($code)->toBe(Command::SUCCESS)
        ->and($tester->getDisplay())->toContain('bare.php')
        ->and($tester->getDisplay())->toContain('empty.php')
        ->and($tester->getDisplay())->toContain('LaravelReady');
});
