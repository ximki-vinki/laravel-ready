<?php

declare(strict_types=1);

use LaravelReady\Console\Commands\AnalyseCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

it('returns success when run without path', function () {
    $tester = new CommandTester(new AnalyseCommand);

    expect($tester->execute([]))->toBe(Command::SUCCESS);
});
