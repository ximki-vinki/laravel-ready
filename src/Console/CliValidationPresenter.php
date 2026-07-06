<?php

declare(strict_types=1);

namespace LaravelReady\Console;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

final class CliValidationPresenter
{
    public function presentAppRoot(mixed $appRoot, Filesystem $filesystem, OutputInterface $output): int
    {
        if (! is_string($appRoot) || $appRoot === '') {
            $this->writeError($output, 'App root is required. Pass --app-root=/path/to/project/app');

            return Command::FAILURE;
        }

        if (! $filesystem->isDirectory($appRoot)) {
            $this->writeError($output, sprintf('App root not found: %s', $appRoot));

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    public function presentPath(string $path, Filesystem $filesystem, OutputInterface $output): int
    {
        if ($filesystem->missing($path)) {
            $this->writeError($output, sprintf('File not found: %s', $path));

            return Command::FAILURE;
        }

        if ($filesystem->isFile($path) && ! Str::endsWith($path, '.php')) {
            $this->writeError($output, 'Expected a PHP file.');

            return Command::INVALID;
        }

        return Command::SUCCESS;
    }

    private function writeError(OutputInterface $output, string $message): void
    {
        $output->writeln(sprintf('<error>%s</error>', $message));
    }
}
