<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260814195438 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE cart_items (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, quantity INTEGER NOT NULL, unit_price NUMERIC(10, 2) NOT NULL, created_at DATETIME NOT NULL, cart_id INTEGER NOT NULL, product_id INTEGER NOT NULL, CONSTRAINT FK_BEF484451AD5CDBF FOREIGN KEY (cart_id) REFERENCES carts (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_BEF484454584665A FOREIGN KEY (product_id) REFERENCES products (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_BEF484451AD5CDBF ON cart_items (cart_id)');
        $this->addSql('CREATE INDEX IDX_BEF484454584665A ON cart_items (product_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_cart_product ON cart_items (cart_id, product_id)');
        $this->addSql('CREATE TABLE carts (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, user_id INTEGER NOT NULL, CONSTRAINT FK_4E004AACA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4E004AACA76ED395 ON carts (user_id)');
        $this->addSql('CREATE TABLE invoices (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, number VARCHAR(50) NOT NULL, total_ht NUMERIC(10, 2) NOT NULL, total_ttc NUMERIC(10, 2) NOT NULL, vat_rate NUMERIC(5, 2) NOT NULL, issued_at DATETIME NOT NULL, created_at DATETIME NOT NULL, order_id INTEGER NOT NULL, user_id INTEGER NOT NULL, CONSTRAINT FK_6A2F2F958D9F6D38 FOREIGN KEY (order_id) REFERENCES orders (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_6A2F2F95A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6A2F2F9596901F54 ON invoices (number)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6A2F2F958D9F6D38 ON invoices (order_id)');
        $this->addSql('CREATE INDEX IDX_6A2F2F95A76ED395 ON invoices (user_id)');
        $this->addSql('CREATE TABLE order_items (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, product_name VARCHAR(150) NOT NULL, unit_price NUMERIC(10, 2) NOT NULL, quantity INTEGER NOT NULL, order_id INTEGER NOT NULL, product_id INTEGER DEFAULT NULL, CONSTRAINT FK_62809DB08D9F6D38 FOREIGN KEY (order_id) REFERENCES orders (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_62809DB04584665A FOREIGN KEY (product_id) REFERENCES products (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_62809DB08D9F6D38 ON order_items (order_id)');
        $this->addSql('CREATE INDEX IDX_62809DB04584665A ON order_items (product_id)');
        $this->addSql('CREATE TABLE orders (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, number VARCHAR(50) NOT NULL, address_line VARCHAR(255) NOT NULL, city VARCHAR(100) NOT NULL, postal_code VARCHAR(20) NOT NULL, country VARCHAR(100) NOT NULL, status VARCHAR(255) NOT NULL, total_amount NUMERIC(10, 2) NOT NULL, vat_rate NUMERIC(4, 2) NOT NULL, paid_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, user_id INTEGER NOT NULL, CONSTRAINT FK_E52FFDEEA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E52FFDEE96901F54 ON orders (number)');
        $this->addSql('CREATE INDEX IDX_E52FFDEEA76ED395 ON orders (user_id)');
        $this->addSql('CREATE TABLE products (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(150) NOT NULL, reference VARCHAR(50) NOT NULL, price NUMERIC(10, 2) NOT NULL, description CLOB NOT NULL, stock INTEGER NOT NULL, image VARCHAR(255) DEFAULT NULL, publisher VARCHAR(100) DEFAULT NULL, is_active BOOLEAN NOT NULL, created_at DATETIME NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B3BA5A5AAEA34913 ON products (reference)');
        $this->addSql('CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(180) NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, roles CLOB NOT NULL, is_deleted BOOLEAN NOT NULL, deleted_at DATETIME DEFAULT NULL, reset_token VARCHAR(64) DEFAULT NULL, reset_token_expires_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9E7927C74 ON users (email)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9D7C8DC19 ON users (reset_token)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE cart_items');
        $this->addSql('DROP TABLE carts');
        $this->addSql('DROP TABLE invoices');
        $this->addSql('DROP TABLE order_items');
        $this->addSql('DROP TABLE orders');
        $this->addSql('DROP TABLE products');
        $this->addSql('DROP TABLE users');
    }
}
