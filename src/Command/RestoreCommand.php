<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\BackupService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:restore', description: 'Restore SQLite database from a backup file')]
class RestoreCommand extends Command
{
    public function __construct(
        private readonly BackupService $backupService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('file', InputArgument::REQUIRED, 'Chemin du fichier de backup à restaurer');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filePath = $input->getArgument('file');

        try {
            $this->backupService->restore($filePath);
            $output->writeln('Base de données restaurée avec succès.');

            return Command::SUCCESS;
        } catch (\RuntimeException $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));

            return Command::FAILURE;
        }
    }
}
