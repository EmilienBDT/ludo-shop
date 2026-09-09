<?php

declare(strict_types=1);

namespace App\Service;

use Doctrine\DBAL\Connection;

class BackupService
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    private function getDatabasePath(): string
    {
        $path = $this->connection->getParams()['path'] ?? null;
        if (null === $path || !file_exists($path)) {
            throw new \RuntimeException('Base de données SQLite introuvable.');
        }

        return $path;
    }

    public function backup(string $filePath): string
    {
        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $sourcePath = $this->getDatabasePath();

        $timestamp = (new \DateTimeImmutable())->format('YmdHis');
        $backupPath = preg_replace('/\.db$/', '_'.$timestamp.'.db', $filePath);

        if (null === $backupPath) {
            throw new \RuntimeException('Erreur lors de la génération du nom de backup.');
        }

        if (!copy($sourcePath, $backupPath)) {
            throw new \RuntimeException(sprintf('Impossible de créer le backup dans %s.', $backupPath));
        }

        return $backupPath;
    }

    public function restore(string $filePath): void
    {
        if (!file_exists($filePath)) {
            throw new \RuntimeException(sprintf('Le fichier %s n\'existe pas.', $filePath));
        }

        $handle = fopen($filePath, 'rb');
        if (false === $handle) {
            throw new \RuntimeException('Erreur : le fichier n\'est pas une base de données SQLite valide.');
        }
        $header = fread($handle, 16);
        fclose($handle);
        if (false === $header || 0 !== strncmp($header, 'SQLite format 3', 15)) {
            throw new \RuntimeException('Erreur : le fichier n\'est pas une base de données SQLite valide.');
        }

        $sourcePath = $this->getDatabasePath();

        $backupOriginal = $sourcePath.'.backup';
        if (!copy($sourcePath, $backupOriginal)) {
            throw new \RuntimeException('Impossible de créer une sauvegarde de sécurité.');
        }

        try {
            if (!copy($filePath, $sourcePath)) {
                throw new \RuntimeException('Erreur lors de la restauration.');
            }
            unlink($backupOriginal);
        } catch (\Throwable $e) {
            if (file_exists($backupOriginal)) {
                copy($backupOriginal, $sourcePath);
                unlink($backupOriginal);
            }
            throw $e;
        }
    }
}
