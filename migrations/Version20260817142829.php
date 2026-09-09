<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260817142829 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add reviews table with UNIQUE(product, user) constraint (RG-29).';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE reviews (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, rating SMALLINT NOT NULL, comment CLOB DEFAULT NULL, is_hidden BOOLEAN DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, product_id INTEGER NOT NULL, user_id INTEGER NOT NULL, CONSTRAINT FK_6970EB0F4584665A FOREIGN KEY (product_id) REFERENCES products (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_6970EB0FA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_6970EB0F4584665A ON reviews (product_id)');
        $this->addSql('CREATE INDEX IDX_6970EB0FA76ED395 ON reviews (user_id)');
        $this->addSql('CREATE UNIQUE INDEX unique_review_product_user ON reviews (product_id, user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE reviews');
    }
}
