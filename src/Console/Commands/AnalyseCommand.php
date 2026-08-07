<?php

declare(strict_types=1);

namespace LaravelReady\Console\Commands;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use LaravelReady\Analysis\AnalyseRunnerFactory;
use LaravelReady\Console\AnalysableFile;
use LaravelReady\Console\CliValidationPresenter;
use LaravelReady\Console\ReadinessPresenter;
use LaravelReady\Project\ProjectConfig;
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
    public function __construct(private AnalyseRunnerFactory $runnerFactory)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'path',
                InputArgument::IS_ARRAY | InputArgument::OPTIONAL,
                'Path to analyse');
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
        $cliValidation = new CliValidationPresenter($output);

        $configPath = getcwd().'/laravel-ready.json';
        $config = $cliValidation->presentProjectConfigLoad($configPath, $filesystem);

        if (! $config instanceof ProjectConfig) {
            return Command::FAILURE;
        }

        $files = collect();

        foreach ($paths as $path) {
            $exitCode = $cliValidation->presentPath($path, $filesystem);

            if ($exitCode !== Command::SUCCESS) {
                return $exitCode;
            }

            $files = $files->merge($this->resolveFiles($filesystem, $path));
        }

        $exitCode = $cliValidation->presentPhpFilesFound($files->isNotEmpty());

        if ($exitCode !== Command::SUCCESS) {
            return $exitCode;
        }

        $runner = $this->runnerFactory->create($config);
        $presenter = new ReadinessPresenter;

        $files->values()->each(function (AnalysableFile $file) use ($output, $runner, $presenter, &$exitCode): void {
            $output->writeln('');

            $readiness = $runner->analyse($file->absolutePath);

            $fileExitCode = $presenter->present($readiness, $file->relativePath, $output);
            if ($fileExitCode !== Command::SUCCESS) {
                $exitCode = $fileExitCode;
            }
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
            ->filter(fn (SplFileInfo $file): bool => Str::endsWith($file->getFilename(), '.php'))
            ->map(fn (SplFileInfo $file): AnalysableFile => AnalysableFile::fromDirectoryEntry($file))
            ->values();
    }
}
