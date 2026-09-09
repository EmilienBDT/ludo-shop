<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260815221148 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add User security & marketing fields: brute-force (RG-45), email verification (RG-49), newsletter opt-in (RG-39).';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__users AS SELECT id, email, password, first_name, last_name, roles, is_deleted, deleted_at, reset_token, reset_token_expires_at, created_at, birth_date FROM users');
        $this->addSql('DROP TABLE users');
        $this->addSql('CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(180) NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, roles CLOB NOT NULL, is_deleted BOOLEAN NOT NULL, deleted_at DATETIME DEFAULT NULL, reset_token VARCHAR(64) DEFAULT NULL, reset_token_expires_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, birth_date DATE DEFAULT NULL, failed_login_attempts INTEGER DEFAULT 0 NOT NULL, locked_until DATETIME DEFAULT NULL, email_verified_at DATETIME DEFAULT NULL, email_verify_token VARCHAR(64) DEFAULT NULL, newsletter_subscribed BOOLEAN DEFAULT 0 NOT NULL, newsletter_token VARCHAR(64) DEFAULT NULL)');
        $this->addSql('INSERT INTO users (id, email, password, first_name, last_name, roles, is_deleted, deleted_at, reset_token, reset_token_expires_at, created_at, birth_date) SELECT id, email, password, first_name, last_name, roles, is_deleted, deleted_at, reset_token, reset_token_expires_at, created_at, birth_date FROM __temp__users');
        $this->addSql('DROP TABLE __temp__users');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9D7C8DC19 ON users (reset_token)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9E7927C74 ON users (email)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9CDF78CC8 ON users (email_verify_token)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9D55AEDCF ON users (newsletter_token)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__users AS SELECT id, email, password, first_name, last_name, roles, is_deleted, birth_date, deleted_at, reset_token, reset_token_expires_at, created_at FROM users');
        $this->addSql('DROP TABLE users');
        $this->addSql('CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(180) NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, roles CLOB NOT NULL, is_deleted BOOLEAN NOT NULL, birth_date DATE DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, reset_token VARCHAR(64) DEFAULT NULL, reset_token_expires_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL)');
        $this->addSql('INSERT INTO users (id, email, password, first_name, last_name, roles, is_deleted, birth_date, deleted_at, reset_token, reset_token_expires_at, created_at) SELECT id, email, password, first_name, last_name, roles, is_deleted, birth_date, deleted_at, reset_token, reset_token_expires_at, created_at FROM __temp__users');
        $this->addSql('DROP TABLE __temp__users');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9E7927C74 ON users (email)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9D7C8DC19 ON users (reset_token)');
    }
}
