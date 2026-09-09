<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260815221941 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create append-only AuditLog table for sensitive actions (RG-47).';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE audit_log (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, "action" VARCHAR(100) NOT NULL, details CLOB NOT NULL, created_at DATETIME NOT NULL, user_id INTEGER NOT NULL, CONSTRAINT FK_F6E1C0F5A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_F6E1C0F5A76ED395 ON audit_log (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE audit_log');
    }
}
