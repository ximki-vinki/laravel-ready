<?php

declare(strict_types=1);

namespace LaravelReady\Console\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
        $path = $input->getArgument('path');

        if ($path === null) {
            return Command::SUCCESS;
        }

        if (! is_file($path)) {
            $output->writeln(sprintf('<error>File not found: %s</error>', $path));

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
