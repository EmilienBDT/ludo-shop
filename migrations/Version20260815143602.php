<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260815143602 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE products ADD COLUMN is_mature BOOLEAN DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE users ADD COLUMN birth_date DATE DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__products AS SELECT id, name, reference, price, description, stock, image, publisher, is_active, created_at FROM products');
        $this->addSql('DROP TABLE products');
        $this->addSql('CREATE TABLE products (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(150) NOT NULL, reference VARCHAR(50) NOT NULL, price NUMERIC(10, 2) NOT NULL, description CLOB NOT NULL, stock INTEGER NOT NULL, image VARCHAR(255) DEFAULT NULL, publisher VARCHAR(100) DEFAULT NULL, is_active BOOLEAN NOT NULL, created_at DATETIME NOT NULL)');
        $this->addSql('INSERT INTO products (id, name, reference, price, description, stock, image, publisher, is_active, created_at) SELECT id, name, reference, price, description, stock, image, publisher, is_active, created_at FROM __temp__products');
        $this->addSql('DROP TABLE __temp__products');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B3BA5A5AAEA34913 ON products (reference)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__users AS SELECT id, email, password, first_name, last_name, roles, is_deleted, deleted_at, reset_token, reset_token_expires_at, created_at FROM users');
        $this->addSql('DROP TABLE users');
        $this->addSql('CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(180) NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, roles CLOB NOT NULL, is_deleted BOOLEAN NOT NULL, deleted_at DATETIME DEFAULT NULL, reset_token VARCHAR(64) DEFAULT NULL, reset_token_expires_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL)');
        $this->addSql('INSERT INTO users (id, email, password, first_name, last_name, roles, is_deleted, deleted_at, reset_token, reset_token_expires_at, created_at) SELECT id, email, password, first_name, last_name, roles, is_deleted, deleted_at, reset_token, reset_token_expires_at, created_at FROM __temp__users');
        $this->addSql('DROP TABLE __temp__users');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9E7927C74 ON users (email)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9D7C8DC19 ON users (reset_token)');
    }
}
