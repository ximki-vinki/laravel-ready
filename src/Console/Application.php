<?php

declare(strict_types=1);

namespace LaravelReady\Console;

use Exception;
use LaravelReady\Analysis\AnalyseRunnerFactory;
use LaravelReady\Console\Commands\AnalyseCommand;
use LaravelReady\ContainerFactory;
use Symfony\Component\Console\Application as ConsoleApplication;

final class Application
{
    public const string VERSION = '@package_version@';

    public static function version(): string
    {
        // Не склеивать в один литерал: после bake sed константа уже другая.
        // @phpstan-ignore identical.alwaysTrue
        if ('@package_version'.'@' === self::VERSION) {
            return '0.0.0-dev';
        }

        // @phpstan-ignore deadCode.unreachable
        return self::VERSION;
    }

    /**
     * @throws Exception
     */
    public static function run(): int
    {
        $app = new ConsoleApplication('laravel-ready', self::version());
        $app->addCommand(new AnalyseCommand(new AnalyseRunnerFactory(new ContainerFactory)));
        $app->setDefaultCommand('laravel-ready', true);

        return $app->run();
    }
}
