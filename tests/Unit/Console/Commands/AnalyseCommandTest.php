<?php

declare(strict_types=1);

use LaravelReady\Console\Commands\AnalyseCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

it('returns success when run without path', function () {
    $tester = new CommandTester(new AnalyseCommand);

    expect($tester->execute([]))->toBe(Command::SUCCESS);
});

it('fails when path does not exist', function () {
    $tester = new CommandTester(new AnalyseCommand);
    $code = $tester->execute(['path' => '/tmp/no-such-file.php']);
    expect($code)->toBe(Command::FAILURE)
        ->and($tester->getDisplay())->toContain('File not found');
});
