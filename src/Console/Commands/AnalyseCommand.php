<?php

declare(strict_types=1);

namespace LaravelReady\Console\Commands;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use LaravelReady\Analysis\LegacyDetector;
use LaravelReady\Console\Output\LaravelReadyOutput;
use LaravelReady\Console\Output\LegacyOutput;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\DescriptorHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\SplFileInfo;

#[AsCommand(
    name: 'laravel-ready',
    description: 'Analyse PHP files for Laravel migration readiness',
)]
final class AnalyseCommand extends Command
{
    protected function configure(): void
    {
        $this->addArgument('path', InputArgument::OPTIONAL, 'Path to analyse');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var ?string $path */
        $path = $input->getArgument('path');

        if ($path === null) {
            (new DescriptorHelper)->describe($output, $this);

            return Command::SUCCESS;
        }

        $filesystem = new Filesystem;

        if ($filesystem->missing($path)) {
            $output->writeln(sprintf('<error>File not found: %s</error>', $path));

            return Command::FAILURE;
        }

        if ($filesystem->isFile($path) && ! Str::endsWith($path, '.php')) {
            $output->writeln('<error>Expected a PHP file.</error>');

            return Command::INVALID;
        }

        $files = $filesystem->isFile($path)
            ? collect([[
                'absolute' => $path,
                'relative' => basename($path),
            ]])
            : collect($filesystem->allFiles($path))
                ->map(fn (SplFileInfo $file): array => [
                    'absolute' => $file->getPathname(),
                    'relative' => $file->getRelativePathname(),
                ]);

        $files->each(function (array $file) use ($output): void {
            $findings = (new LegacyDetector)->analyse($file['absolute']);

            if ($findings->isNotEmpty()) {
                (new LegacyOutput)->write($output, $findings, $file['relative']);
            } else {
                (new LaravelReadyOutput)->write($output, $file['relative']);
            }
        });

        return Command::SUCCESS;
    }
}
