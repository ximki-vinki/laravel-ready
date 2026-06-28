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
    $code = $tester->execute(['path' => '/tmp/laravel-ready-missing-'.uniqid().'.php']);
    expect($code)->toBe(Command::FAILURE)
        ->and($tester->getDisplay())->toContain('File not found');
});

it('returns invalid when path is not a php file', function () {
    $tester = new CommandTester(new AnalyseCommand);

    $code = $tester->execute(['path' => fixture('not-php.txt')]);

    expect($code)->toBe(Command::INVALID)
        ->and($tester->getDisplay())->toContain('Expected a PHP file');
});

it('prints legacy level and finding for globals fixture', function () {
    $tester = new CommandTester(new AnalyseCommand);

    $code = $tester->execute(['path' => fixture('Legacy/Superglobals/bare.php')]);

    expect($code)->toBe(Command::SUCCESS)
        ->and($tester->getDisplay())->toContain('Legacy')
        ->and($tester->getDisplay())->toContain('$GLOBALS');
});

it('prints laravel ready level for clean fixture', function () {
    $tester = new CommandTester(new AnalyseCommand);

    $tester->execute(['path' => fixture('Legacy/empty.php')]);

    expect($tester->getDisplay())->toContain('LaravelReady')
        ->and($tester->getDisplay())->not->toContain('$GLOBALS');
});
