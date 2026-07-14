<?php

declare(strict_types=1);

use LaravelReady\Console\Commands\AnalyseCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

covers(AnalyseCommand::class);

it('returns success when run without path', function (): void {
    $tester = new CommandTester(new AnalyseCommand);

    $code = $tester->execute([]);

    expect($code)->toBe(Command::SUCCESS)
        ->and($tester->getDisplay())->toContain('Arguments:');
});

it('fails when app root is missing', function (): void {
    $tester = new CommandTester(new AnalyseCommand);

    $code = $tester->execute(['path' => [fixture('Legacy/Clean/empty.php')]]);

    expect($code)->toBe(Command::FAILURE)
        ->and($tester->getDisplay())->toContain('App root is required. Pass --app-root=/path/to/project/app');
});

it('fails when app root does not exist', function (): void {
    $tester = new CommandTester(new AnalyseCommand);

    $code = $tester->execute([
        'path' => [fixture('Legacy/Clean/empty.php')],
        '--app-root' => '/tmp/laravel-ready-missing-root-'.uniqid(),
    ]);

    expect($code)->toBe(Command::FAILURE)
        ->and($tester->getDisplay())->toContain('App root not found:');
});

it('fails when path does not exist', function (): void {
    $tester = new CommandTester(new AnalyseCommand);
    $code = $tester->execute([
        'path' => ['/tmp/laravel-ready-missing-'.uniqid().'.php'],
        '--app-root' => appRoot(),
    ]);
    expect($code)->toBe(Command::FAILURE)
        ->and($tester->getDisplay())->toContain('File not found');
});

it('returns invalid when path is not a php file', function (): void {
    $tester = new CommandTester(new AnalyseCommand);

    $code = $tester->execute([
        'path' => [fixture('not-php.txt')],
        '--app-root' => appRoot(),
    ]);

    expect($code)->toBe(Command::INVALID)
        ->and($tester->getDisplay())->toContain('Expected a PHP file');
});

it('returns success for laravel-ready fixture without blockers', function (): void {
    $tester = new CommandTester(new AnalyseCommand);

    $code = $tester->execute([
        'path' => [fixture('Tags/laravel-ready/class.php')],
        '--app-root' => appRoot(),
    ]);

    expect($code)->toBe(Command::SUCCESS)
        ->and($tester->getDisplay())->toContain('class.php : LaravelReady');
});

it('returns success for laravel-adapter fixture without blockers', function (): void {
    $tester = new CommandTester(new AnalyseCommand);

    $code = $tester->execute([
        'path' => [fixture('Tags/laravel-adapter/class.php')],
        '--app-root' => appRoot(),
    ]);

    expect($code)->toBe(Command::SUCCESS)
        ->and($tester->getDisplay())->toContain('class.php : LaravelAdapter');
});

it('returns success for legacy-adapter fixture and hides legacy findings', function (): void {
    $tester = new CommandTester(new AnalyseCommand);

    $code = $tester->execute([
        'path' => [fixture('Tags/legacy-adapter/with-blocker.php')],
        '--app-root' => appRoot(),
    ]);

    expect($code)->toBe(Command::SUCCESS)
        ->and($tester->getDisplay())->toContain('with-blocker.php : LegacyAdapter')
        ->and($tester->getDisplay())->not->toContain('$_GET');
});

it('returns success for legacy-perfect fixture', function (): void {
    $tester = new CommandTester(new AnalyseCommand);

    $code = $tester->execute([
        'path' => [fixture('Tags/legacy-perfect/class.php')],
        '--app-root' => appRoot(),
    ]);

    expect($code)->toBe(Command::SUCCESS)
        ->and($tester->getDisplay())->toContain('class.php : LegacyPerfect');
});

it('returns failure when legacy-perfect has ast blocker', function (): void {
    $tester = new CommandTester(new AnalyseCommand);

    $code = $tester->execute([
        'path' => [fixture('Tags/legacy-perfect/with-blocker.php')],
        '--app-root' => appRoot(),
    ]);

    expect($code)->toBe(Command::FAILURE)
        ->and($tester->getDisplay())->toContain('with-blocker.php : LegacyPerfect')
        ->and($tester->getDisplay())->toContain('$_GET')
        ->and($tester->getDisplay())->toContain('Guard failed: @legacy-perfect file must stay cleaned in legacy contour.');
});

it('returns failure when legacy-perfect imports laravel-ready', function (): void {
    $tester = new CommandTester(new AnalyseCommand);

    $code = $tester->execute([
        'path' => [fixture('Use/project/app/Domain/PerfectUsesReady.php')],
        '--app-root' => appRoot(),
    ]);

    expect($code)->toBe(Command::FAILURE)
        ->and($tester->getDisplay())->toContain('Guard failed: @legacy-perfect file must stay cleaned in legacy contour.')
        ->and($tester->getDisplay())->toContain('App\Domain\TaggedService');
});

it('returns failure when legacy-adapter imports laravel-ready', function (): void {
    $tester = new CommandTester(new AnalyseCommand);

    $code = $tester->execute([
        'path' => [fixture('Use/project/app/Adapter/UsesReady.php')],
        '--app-root' => appRoot(),
    ]);

    expect($code)->toBe(Command::FAILURE)
        ->and($tester->getDisplay())->toContain('Guard failed: @legacy-adapter file must stay in legacy contour.')
        ->and($tester->getDisplay())->toContain('App\Domain\TaggedService');
});

it('returns success for legacy-code fixture with legacy finding', function (): void {
    $tester = new CommandTester(new AnalyseCommand);

    $code = $tester->execute([
        'path' => [fixture('Tags/Mixed/tag-and-blocker.php')],
        '--app-root' => appRoot(),
    ]);

    expect($code)->toBe(Command::SUCCESS)
        ->and($tester->getDisplay())->toContain('var: $_GET');
});

it('returns failure when laravel-ready fixture has legacy blocker', function (): void {
    $tester = new CommandTester(new AnalyseCommand);

    $code = $tester->execute([
        'path' => [fixture('Tags/laravel-ready/with-blocker.php')],
        '--app-root' => appRoot(),
    ]);

    expect($code)->toBe(Command::FAILURE)
        ->and($tester->getDisplay())->toContain('Guard failed: @laravel-ready file must stay LaravelReady.');
});

it('returns failure when laravel-adapter fixture has legacy blocker', function (): void {
    $tester = new CommandTester(new AnalyseCommand);

    $code = $tester->execute([
        'path' => [fixture('Tags/laravel-adapter/with-blocker.php')],
        '--app-root' => appRoot(),
    ]);

    expect($code)->toBe(Command::FAILURE)
        ->and($tester->getDisplay())->toContain('Guard failed: @laravel-adapter file must stay LaravelAdapter.');
});

it('returns success when laravel-adapter has blockers but skipCheck', function (): void {
    $tester = new CommandTester(new AnalyseCommand);

    $code = $tester->execute([
        'path' => [fixture('Tags/laravel-adapter/skip-check-with-blocker.php')],
        '--app-root' => appRoot(),
    ]);

    expect($code)->toBe(Command::SUCCESS)
        ->and($tester->getDisplay())->toContain('var: $_GET')
        ->and($tester->getDisplay())->toContain('Skipped: @skipCheck.');
});

it('returns failure for file with multiple tags', function (): void {
    $tester = new CommandTester(new AnalyseCommand);

    $code = $tester->execute([
        'path' => [fixture('Tags/Mixed/multi-tag.php')],
        '--app-root' => appRoot(),
    ]);

    expect($code)->toBe(Command::FAILURE)
        ->and($tester->getDisplay())->toContain('MultiTag failed: file must have only one tag.');
});

it('returns failure for untagged file', function (): void {
    $tester = new CommandTester(new AnalyseCommand);

    $code = $tester->execute([
        'path' => [fixture('Legacy/Clean/empty.php')],
        '--app-root' => appRoot(),
    ]);

    expect($code)->toBe(Command::FAILURE)
        ->and($tester->getDisplay())->toContain('Not guarded: file has no tag.');
});

it('prints denied use import for guarded file', function (): void {
    $tester = new CommandTester(new AnalyseCommand);

    $code = $tester->execute([
        'path' => [fixture('Use/project/app/Domain/Invoice.php')],
        '--app-root' => appRoot(),
    ]);

    expect($code)->toBe(Command::FAILURE)
        ->and($tester->getDisplay())->toContain('use: Wf\Legacy\OldRepo (line 5)');
});

it('prints denied use import for untagged app class when app root is passed', function (): void {
    $tester = new CommandTester(new AnalyseCommand);

    $code = $tester->execute([
        'path' => [fixture('Use/project/app/Consumer/UsesUntagged.php')],
        '--app-root' => appRoot(),
    ]);

    expect($code)->toBe(Command::FAILURE)
        ->and($tester->getDisplay())->toContain('use: App\Domain\UntaggedService (line 5)');
});

it('analyses php files in subdirectories', function (): void {
    $tester = new CommandTester(new AnalyseCommand);

    $code = $tester->execute([
        'path' => [fixture('Legacy')],
        '--app-root' => appRoot(),
    ]);

    expect($code)->toBe(Command::FAILURE)
        ->and($tester->getDisplay())->toContain('Superglobals/bare.php')
        ->and($tester->getDisplay())->toContain('Clean/empty.php');
});

it('analyses multiple file paths passed as separate arguments', function (): void {
    $tester = new CommandTester(new AnalyseCommand);

    $code = $tester->execute([
        'path' => [
            fixture('Legacy/Superglobals/bare.php'),
            fixture('Legacy/Clean/empty.php'),
        ],
        '--app-root' => appRoot(),
    ]);

    expect($code)->toBe(Command::FAILURE)
        ->and($tester->getDisplay())->toContain("Not guarded: file has no tag.\n\nempty.php");
});
