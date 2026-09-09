<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260817144239 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE products ADD COLUMN promo_price NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE products ADD COLUMN promo_starts_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE products ADD COLUMN promo_ends_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__products AS SELECT id, name, reference, price, description, stock, image, publisher, is_active, is_mature, playtime_minutes, setup_minutes, min_age, max_age, min_players, max_players, created_at FROM products');
        $this->addSql('DROP TABLE products');
        $this->addSql('CREATE TABLE products (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(150) NOT NULL, reference VARCHAR(50) NOT NULL, price NUMERIC(10, 2) NOT NULL, description CLOB NOT NULL, stock INTEGER NOT NULL, image VARCHAR(255) DEFAULT NULL, publisher VARCHAR(100) DEFAULT NULL, is_active BOOLEAN NOT NULL, is_mature BOOLEAN DEFAULT 0 NOT NULL, playtime_minutes INTEGER DEFAULT NULL, setup_minutes INTEGER DEFAULT NULL, min_age INTEGER DEFAULT NULL, max_age INTEGER DEFAULT NULL, min_players INTEGER DEFAULT NULL, max_players INTEGER DEFAULT NULL, created_at DATETIME NOT NULL)');
        $this->addSql('INSERT INTO products (id, name, reference, price, description, stock, image, publisher, is_active, is_mature, playtime_minutes, setup_minutes, min_age, max_age, min_players, max_players, created_at) SELECT id, name, reference, price, description, stock, image, publisher, is_active, is_mature, playtime_minutes, setup_minutes, min_age, max_age, min_players, max_players, created_at FROM __temp__products');
        $this->addSql('DROP TABLE __temp__products');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B3BA5A5AAEA34913 ON products (reference)');
    }
}
