<?php

declare(strict_types=1);

namespace LaravelReady\Console;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

final readonly class CliValidationPresenter
{
    public function __construct(
        private OutputInterface $output,
    ) {}

    public function presentAppRoot(mixed $appRoot, Filesystem $filesystem): int
    {
        if (! is_string($appRoot) || $appRoot === '') {
            $this->writeError('App root is required. Pass --app-root=/path/to/project/app');

            return Command::FAILURE;
        }

        if (! $filesystem->isDirectory($appRoot)) {
            $this->writeError(sprintf('App root not found: %s', $appRoot));

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    public function presentPath(string $path, Filesystem $filesystem): int
    {
        if ($filesystem->missing($path)) {
            $this->writeError(sprintf('File not found: %s', $path));

            return Command::FAILURE;
        }

        if ($filesystem->isFile($path) && ! Str::endsWith($path, '.php')) {
            $this->writeError('Expected a PHP file.');

            return Command::INVALID;
        }

        return Command::SUCCESS;
    }

    public function presentPhpFilesFound(bool $hasPhpFiles): int
    {
        if (! $hasPhpFiles) {
            $this->writeError('PHP files not found');

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function writeError(string $message): void
    {
        $this->output->writeln(sprintf('<error>%s</error>', $message));
    }
}
