<?php

declare(strict_types=1);

namespace LaravelReady\Console;

use Exception;
use LaravelReady\Console\Commands\AnalyseCommand;
use Symfony\Component\Console\Application as ConsoleApplication;

final class Application
{
    /**
     * @throws Exception
     */
    public static function run(): int
    {
        $app = new ConsoleApplication('laravel-ready', '0.1.0');
        $app->addCommand(new AnalyseCommand);
        $app->setDefaultCommand('laravel-ready', true);

        return $app->run();
    }
}
