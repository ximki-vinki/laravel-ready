<?php

declare(strict_types=1);

namespace LaravelReady\Console\Commands;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use LaravelReady\Analysis\Detector;
use LaravelReady\Console\AnalysableFile;
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
        $this->addArgument('path', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, 'Path to analyse');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var list<string> $paths */
        $paths = $input->getArgument('path');

        if ($paths === []) {
            (new DescriptorHelper)->describe($output, $this);

            return Command::SUCCESS;
        }

        $filesystem = new Filesystem;
        $files = collect();

        foreach ($paths as $path) {
            $exitCode = $this->validatePath($filesystem, $output, $path);

            if ($exitCode !== null) {
                return $exitCode;
            }

            $files = $files->merge($this->resolveFiles($filesystem, $path));
        }

        $files->each(function (AnalysableFile $file) use ($output): void {
            $result = (new Detector)->analyse($file->absolutePath);

            if ($result->findings->isNotEmpty()) {
                (new LegacyOutput)->write($output, $result->findings, $file->relativePath);
            } else {
                (new LaravelReadyOutput)->write($output, $file->relativePath);
            }
        });

        return Command::SUCCESS;
    }

    private function validatePath(Filesystem $filesystem, OutputInterface $output, string $path): ?int
    {
        if ($filesystem->missing($path)) {
            $output->writeln(sprintf('<error>File not found: %s</error>', $path));

            return Command::FAILURE;
        }

        if ($filesystem->isFile($path) && ! Str::endsWith($path, '.php')) {
            $output->writeln('<error>Expected a PHP file.</error>');

            return Command::INVALID;
        }

        return null;
    }

    /**
     * @return Collection<int, AnalysableFile>
     */
    private function resolveFiles(Filesystem $filesystem, string $path): Collection
    {
        if ($filesystem->isFile($path)) {
            return collect([AnalysableFile::fromExplicitFile($path)]);
        }

        return collect($filesystem->allFiles($path))
            ->map(fn (SplFileInfo $file): AnalysableFile => AnalysableFile::fromDirectoryEntry($file));
    }
}
