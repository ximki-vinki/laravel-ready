<?php

declare(strict_types=1);

namespace LaravelReady\Console\Commands;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use LaravelReady\Analysis\Detector;
use LaravelReady\Analysis\Readiness\ReadinessResolver;
use LaravelReady\Console\AnalysableFile;
use LaravelReady\Console\CliValidationPresenter;
use LaravelReady\Console\ReadinessPresenter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\DescriptorHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
        $this
            ->addArgument(
                'path',
                InputArgument::IS_ARRAY | InputArgument::OPTIONAL,
                'Path to analyse')
            ->addOption(
                'app-root',
                null,
                InputOption::VALUE_REQUIRED,
                'Root directory of App\\ code (e.g. project/app in KDL.Site)',
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var list<string> $paths */
        $paths = $input->getArgument('path');

        if ($paths === []) {
            (new DescriptorHelper)->describe($output, $this);

            return Command::SUCCESS; // @pest-mutate-ignore: RemoveEarlyReturn
        }

        $filesystem = new Filesystem;
        $cliValidation = new CliValidationPresenter;
        $appRoot = $input->getOption('app-root');
        $exitCode = $cliValidation->presentAppRoot($appRoot, $filesystem, $output);

        if ($exitCode !== Command::SUCCESS) {
            return $exitCode;
        }

        /** @var string $appRoot */

        $files = collect();

        foreach ($paths as $path) {
            $exitCode = $cliValidation->presentPath($path, $filesystem, $output);

            if ($exitCode !== Command::SUCCESS) {
                return $exitCode;
            }

            $files = $files->merge($this->resolveFiles($filesystem, $path));
        }

        $files->values()->each(function (AnalysableFile $file) use ($output, $appRoot, &$exitCode): void {
            $output->writeln('');

            $result = (new Detector)->analyse($file->absolutePath);

            $readiness = (new ReadinessResolver)->resolve($result, $appRoot);

            $exitCode = (new ReadinessPresenter)->present($readiness, $file->relativePath, $output);
        });

        return $exitCode;
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
