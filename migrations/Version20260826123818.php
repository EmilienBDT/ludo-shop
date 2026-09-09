<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260826123818 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE order_status_history (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, status VARCHAR(255) NOT NULL, changed_at DATETIME NOT NULL, order_id INTEGER NOT NULL, changed_by_id INTEGER DEFAULT NULL, CONSTRAINT FK_471AD77E8D9F6D38 FOREIGN KEY (order_id) REFERENCES orders (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_471AD77E828AD0A0 FOREIGN KEY (changed_by_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_471AD77E8D9F6D38 ON order_status_history (order_id)');
        $this->addSql('CREATE INDEX IDX_471AD77E828AD0A0 ON order_status_history (changed_by_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE order_status_history');
    }
}
