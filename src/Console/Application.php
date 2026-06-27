<?php

declare(strict_types=1);

namespace LaravelReady\Console;

use Exception;
use LaravelReady\Console\Commands\AnalyseCommand;
use Symfony\Component\Console\Application as ConsoleApplication;

final class Application
{
    public const string VERSION = '0.1.0';

    /**
     * @throws Exception
     */
    public static function run(): int
    {
        $app = new ConsoleApplication('laravel-ready', self::VERSION);
        $app->addCommand(new AnalyseCommand);
        $app->setDefaultCommand('laravel-ready', true);

        return $app->run();
    }
}
