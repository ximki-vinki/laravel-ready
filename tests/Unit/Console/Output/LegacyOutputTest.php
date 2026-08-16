<?php

declare(strict_types=1);

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

beforeEach(function (): void {
    chdir(projectRoot().'/tests/Fixtures/Use');
});

afterEach(function (): void {
    chdir(projectRoot());
});

it('prints superglobals in var group', function (): void {
    $tester = new CommandTester(analyseCommand());

    $code = $tester->execute([
        'path' => [fixture('Legacy/Superglobals/bare.php')],
    ]);

    expect($code)->toBe(Command::FAILURE)
        ->and($tester->getDisplay())->toContain('bare.php')
        ->and($tester->getDisplay())->toContain('Untagged')
        ->and($tester->getDisplay())->toContain('var: $GLOBALS (line 3), $_COOKIE (line 4)')
        ->and($tester->getDisplay())->not->toContain('func:');
});

it('prints global variables in global group', function (): void {
    $tester = new CommandTester(analyseCommand());

    $code = $tester->execute([
        'path' => [fixture('Legacy/Global/bare.php')],
    ]);

    expect($code)->toBe(Command::FAILURE)
        ->and($tester->getDisplay())->toContain('bare.php')
        ->and($tester->getDisplay())->toContain('Untagged')
        ->and($tester->getDisplay())->toContain('global: $foo (line 3)')
        ->and($tester->getDisplay())->not->toContain('var:')
        ->and($tester->getDisplay())->not->toContain('func:');
});

it('prints blocked functions in func group', function (): void {
    $tester = new CommandTester(analyseCommand());

    $code = $tester->execute([
        'path' => [fixture('Legacy/Functions/bare.php')],
    ]);

    expect($code)->toBe(Command::FAILURE)
        ->and($tester->getDisplay())->toContain('bare.php')
        ->and($tester->getDisplay())->toContain('Untagged')
        ->and($tester->getDisplay())->toContain('func: define() (line 3)')
        ->and($tester->getDisplay())->not->toContain('var:');
});

it('prints grouped legacy findings for mixed fixture', function (): void {
    $tester = new CommandTester(analyseCommand());

    $code = $tester->execute([
        'path' => [fixture('Legacy/Mixed/rules.php')],
    ]);

    expect($code)->toBe(Command::FAILURE)
        ->and($tester->getDisplay())->toContain('rules.php')
        ->and($tester->getDisplay())->toContain('Untagged')
        ->and($tester->getDisplay())->toContain('var: $_GET (line 3)')
        ->and($tester->getDisplay())->toContain('func: define() (line 4)');
});
