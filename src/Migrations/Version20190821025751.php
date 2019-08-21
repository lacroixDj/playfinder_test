<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190821025751 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('CREATE TABLE currency (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, code VARCHAR(8) NOT NULL, name VARCHAR(255) NOT NULL, symbol VARCHAR(8) NOT NULL)');
        $this->addSql('CREATE TABLE pitch (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, sport_id INTEGER NOT NULL, name VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE INDEX IDX_279FBED9AC78BCF8 ON pitch (sport_id)');
        $this->addSql('CREATE TABLE slot (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, currency_id INTEGER NOT NULL, pitch_id INTEGER NOT NULL, starts DATETIME NOT NULL, ends DATETIME NOT NULL, price DOUBLE PRECISION NOT NULL, available BOOLEAN NOT NULL)');
        $this->addSql('CREATE INDEX IDX_AC0E206738248176 ON slot (currency_id)');
        $this->addSql('CREATE INDEX IDX_AC0E2067FEEFC64B ON slot (pitch_id)');
        $this->addSql('CREATE TABLE sport (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(64) NOT NULL)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP TABLE currency');
        $this->addSql('DROP TABLE pitch');
        $this->addSql('DROP TABLE slot');
        $this->addSql('DROP TABLE sport');
    }
}
