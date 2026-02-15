<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260215214121 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE currency_accounts (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, business_partner_id INTEGER NOT NULL, currency VARCHAR(3) NOT NULL, balance NUMERIC(10, 2) NOT NULL, CONSTRAINT FK_FAA0C8BF5330F055 FOREIGN KEY (business_partner_id) REFERENCES business_partners (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_FAA0C8BF5330F055 ON currency_accounts (business_partner_id)');
        $this->addSql('CREATE UNIQUE INDEX unique_bp_currency ON currency_accounts (business_partner_id, currency)');
        $this->addSql('CREATE TABLE currency_exchanges (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, business_partner_id INTEGER NOT NULL, sell_transaction_id INTEGER DEFAULT NULL, buy_transaction_id INTEGER DEFAULT NULL, from_currency VARCHAR(3) NOT NULL, to_currency VARCHAR(3) NOT NULL, from_amount NUMERIC(10, 2) NOT NULL, to_amount NUMERIC(10, 2) NOT NULL, exchange_rate NUMERIC(10, 6) NOT NULL, date DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , executed BOOLEAN NOT NULL, CONSTRAINT FK_B68A14AB5330F055 FOREIGN KEY (business_partner_id) REFERENCES business_partners (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_B68A14AB5E55B330 FOREIGN KEY (sell_transaction_id) REFERENCES transactions (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_B68A14AB336966C1 FOREIGN KEY (buy_transaction_id) REFERENCES transactions (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_B68A14AB5330F055 ON currency_exchanges (business_partner_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B68A14AB5E55B330 ON currency_exchanges (sell_transaction_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B68A14AB336966C1 ON currency_exchanges (buy_transaction_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__business_partners AS SELECT id, name, status, legal_form, address, city, zip, country FROM business_partners');
        $this->addSql('DROP TABLE business_partners');
        $this->addSql('CREATE TABLE business_partners (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, legal_form VARCHAR(255) NOT NULL, address VARCHAR(70) NOT NULL, city VARCHAR(35) NOT NULL, zip VARCHAR(16) NOT NULL, country VARCHAR(2) NOT NULL)');
        $this->addSql('INSERT INTO business_partners (id, name, status, legal_form, address, city, zip, country) SELECT id, name, status, legal_form, address, city, zip, country FROM __temp__business_partners');
        $this->addSql('DROP TABLE __temp__business_partners');
        $this->addSql('CREATE TEMPORARY TABLE __temp__transactions AS SELECT id, business_partner_id, amount, name, date, executed, type, country, iban FROM transactions');
        $this->addSql('DROP TABLE transactions');
        $this->addSql('CREATE TABLE transactions (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, business_partner_id INTEGER NOT NULL, amount NUMERIC(10, 2) NOT NULL, name VARCHAR(255) NOT NULL, date DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , executed BOOLEAN NOT NULL, type VARCHAR(50) NOT NULL, country VARCHAR(2) NOT NULL, iban VARCHAR(34) NOT NULL, currency VARCHAR(3) NOT NULL, CONSTRAINT FK_723705D15330F055 FOREIGN KEY (business_partner_id) REFERENCES business_partners (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO transactions (id, business_partner_id, amount, name, date, executed, type, country, iban) SELECT id, business_partner_id, amount, name, date, executed, type, country, iban FROM __temp__transactions');
        $this->addSql('DROP TABLE __temp__transactions');
        $this->addSql('CREATE INDEX IDX_EAA81A4C5330F055 ON transactions (business_partner_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE currency_accounts');
        $this->addSql('DROP TABLE currency_exchanges');
        $this->addSql('ALTER TABLE business_partners ADD COLUMN balance NUMERIC(10, 2) NOT NULL');
        $this->addSql('CREATE TEMPORARY TABLE __temp__transactions AS SELECT id, business_partner_id, amount, name, date, executed, type, country, iban FROM transactions');
        $this->addSql('DROP TABLE transactions');
        $this->addSql('CREATE TABLE transactions (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, business_partner_id INTEGER NOT NULL, amount NUMERIC(10, 2) NOT NULL, name VARCHAR(255) NOT NULL, date DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , executed BOOLEAN NOT NULL, type VARCHAR(50) NOT NULL, country VARCHAR(2) NOT NULL, iban VARCHAR(34) NOT NULL, CONSTRAINT FK_EAA81A4C5330F055 FOREIGN KEY (business_partner_id) REFERENCES business_partners (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO transactions (id, business_partner_id, amount, name, date, executed, type, country, iban) SELECT id, business_partner_id, amount, name, date, executed, type, country, iban FROM __temp__transactions');
        $this->addSql('DROP TABLE __temp__transactions');
        $this->addSql('CREATE INDEX IDX_723705D15330F055 ON transactions (business_partner_id)');
    }
}
