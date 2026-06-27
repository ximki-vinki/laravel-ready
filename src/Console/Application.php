<?php

declare(strict_types=1);

namespace LaravelReady\Console;

final class Application
{
    /**
     * @param list<string> $argv
     */
    public static function run(array $argv): int
    {
        fwrite(STDOUT, "ok\n");

        return 0;
    }
}
