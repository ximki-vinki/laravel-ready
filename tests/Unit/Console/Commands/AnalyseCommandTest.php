<?php

declare(strict_types=1);

use LaravelReady\Console\Commands\AnalyseCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

covers(AnalyseCommand::class);

it('returns success when run without path', function () {
    $tester = new CommandTester(new AnalyseCommand);

    $code = $tester->execute([]);

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
        ->and($tester->getDisplay())->toContain('class.php : LaravelReady');
});

it('returns success for laravel-adapter fixture without blockers', function () {
    $tester = new CommandTester(new AnalyseCommand);

    $code = $tester->execute(['path' => [fixture('Tags/laravel-adapter/class.php')]]);

    expect($code)->toBe(Command::SUCCESS)
        ->and($tester->getDisplay())->toContain('class.php : LaravelAdapter');
});

it('returns success for legacy-code fixture with legacy finding', function () {
    $tester = new CommandTester(new AnalyseCommand);

    $code = $tester->execute(['path' => [fixture('Tags/Mixed/tag-and-blocker.php')]]);

    expect($code)->toBe(Command::SUCCESS)
        ->and($tester->getDisplay())->toContain('var: $_GET');
});

it('returns failure when laravel-ready fixture has legacy blocker', function () {
    $tester = new CommandTester(new AnalyseCommand);

    $code = $tester->execute(['path' => [fixture('Tags/laravel-ready/with-blocker.php')]]);

    expect($code)->toBe(Command::FAILURE)
        ->and($tester->getDisplay())->toContain('Guard failed: @laravel-ready file must stay LaravelReady.');
});

it('returns failure when laravel-adapter fixture has legacy blocker', function () {
    $tester = new CommandTester(new AnalyseCommand);

    $code = $tester->execute(['path' => [fixture('Tags/laravel-adapter/with-blocker.php')]]);

    expect($code)->toBe(Command::FAILURE)
        ->and($tester->getDisplay())->toContain('Guard failed: @laravel-adapter file must stay LaravelAdapter.');
});

it('returns failure for file with multiple tags', function () {
    $tester = new CommandTester(new AnalyseCommand);

    $code = $tester->execute(['path' => [fixture('Tags/Mixed/multi-tag.php')]]);

    expect($code)->toBe(Command::FAILURE)
        ->and($tester->getDisplay())->toContain('MultiTag failed: file must have only one tag.');
});

it('returns failure for untagged file', function () {
    $tester = new CommandTester(new AnalyseCommand);

    $code = $tester->execute(['path' => [fixture('Legacy/Clean/empty.php')]]);

    expect($code)->toBe(Command::FAILURE)
        ->and($tester->getDisplay())->toContain('Not guarded: file has no tag.');
});

it('prints denied use import for guarded file', function () {
    $tester = new CommandTester(new AnalyseCommand);

    $code = $tester->execute(['path' => [fixture('Use/src/Domain/Invoice.php')]]);

    expect($code)->toBe(Command::FAILURE)
        ->and($tester->getDisplay())->toContain('use: Wf\Legacy\OldRepo (line 5)');
});

it('analyses php files in subdirectories', function () {
    $tester = new CommandTester(new AnalyseCommand);

    $code = $tester->execute(['path' => [fixture('Legacy')]]);

    expect($code)->toBe(Command::FAILURE)
        ->and($tester->getDisplay())->toContain('Superglobals/bare.php')
        ->and($tester->getDisplay())->toContain('Clean/empty.php');
});

it('analyses multiple file paths passed as separate arguments', function () {
    $tester = new CommandTester(new AnalyseCommand);

    $code = $tester->execute([
        'path' => [
            fixture('Legacy/Superglobals/bare.php'),
            fixture('Legacy/Clean/empty.php'),
        ],
    ]);

    expect($code)->toBe(Command::FAILURE)
        ->and($tester->getDisplay())->toContain("Not guarded: file has no tag.\n\nempty.php");
});
