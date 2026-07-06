<?php

declare(strict_types=1);

namespace LaravelReady\Console;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

final class CliValidationPresenter
{
    public function presentProjectRoot(mixed $projectRoot, Filesystem $filesystem, OutputInterface $output): int
    {
        if (! is_string($projectRoot) || $projectRoot === '') {
            $this->writeError($output, 'Project root is required. Pass --project-root=/path/to/project');

            return Command::FAILURE;
        }

        if (! $filesystem->isDirectory($projectRoot)) {
            $this->writeError($output, sprintf('Project root not found: %s', $projectRoot));

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
