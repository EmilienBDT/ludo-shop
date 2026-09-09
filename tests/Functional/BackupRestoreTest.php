<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Command\BackupCommand;
use App\Command\RestoreCommand;
use App\Service\BackupService;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * D-05 — RG-44 / UC-33: backup and restore database.
 */
class BackupRestoreTest extends FunctionalTestCase
{
    private function getCommandTester(string $commandName): CommandTester
    {
        return match ($commandName) {
            'app:backup' => new CommandTester(new BackupCommand(
                $this->client->getContainer()->get(BackupService::class),
            )),
            'app:restore' => new CommandTester(new RestoreCommand(
                $this->client->getContainer()->get(BackupService::class),
            )),
            default => throw new \InvalidArgumentException(sprintf('Unknown command "%s".', $commandName)),
        };
    }

    public function testBackupCreatesValidFile(): void
    {
        $tester = $this->getCommandTester('app:backup');
        $tempFile = tempnam(sys_get_temp_dir(), 'backup_');
        @unlink($tempFile);

        $tester->execute(['file' => $tempFile]);

        $this->assertStringContainsString('Backup créé', $tester->getDisplay());

        $createdFiles = glob($tempFile.'*');
        $this->assertNotEmpty($createdFiles);

        foreach ($createdFiles as $file) {
            @unlink($file);
        }
    }

    public function testRestoreFromValidBackup(): void
    {
        $backupTester = $this->getCommandTester('app:backup');
        $tempFile = tempnam(sys_get_temp_dir(), 'backup_');
        @unlink($tempFile);

        $backupTester->execute(['file' => $tempFile]);
        $createdFiles = glob($tempFile.'*');
        $backupFile = $createdFiles[0] ?? null;
        $this->assertNotNull($backupFile);

        $restoreTester = $this->getCommandTester('app:restore');
        $restoreTester->execute(['file' => $backupFile]);

        $this->assertStringContainsString('restaurée avec succès', $restoreTester->getDisplay());

        @unlink($backupFile);
    }

    public function testRestoreFromInvalidFileFails(): void
    {
        $tester = $this->getCommandTester('app:restore');
        $tempFile = tempnam(sys_get_temp_dir(), 'bad_backup_');
        file_put_contents($tempFile, 'not a sqlite file');

        $tester->execute(['file' => $tempFile]);

        $this->assertStringContainsString('Erreur', $tester->getDisplay());

        @unlink($tempFile);
    }

    public function testRestoreFromNonexistentFileFails(): void
    {
        $tester = $this->getCommandTester('app:restore');

        $tester->execute(['file' => '/nonexistent/path/file.db']);

        $this->assertStringContainsString('n\'existe pas', $tester->getDisplay());
    }
}
