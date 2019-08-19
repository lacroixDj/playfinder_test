<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190819194604 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE currency (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(8) NOT NULL, name VARCHAR(255) NOT NULL, symbol VARCHAR(8) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pitch (id INT AUTO_INCREMENT NOT NULL, sport_id INT NOT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_279FBED9AC78BCF8 (sport_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE slot (id INT AUTO_INCREMENT NOT NULL, currency_id INT NOT NULL, pitch_id INT NOT NULL, starts DATETIME NOT NULL, ends DATETIME NOT NULL, price DOUBLE PRECISION NOT NULL, available TINYINT(1) NOT NULL, INDEX IDX_AC0E206738248176 (currency_id), INDEX IDX_AC0E2067FEEFC64B (pitch_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sport (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(64) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pitch ADD CONSTRAINT FK_279FBED9AC78BCF8 FOREIGN KEY (sport_id) REFERENCES sport (id)');
        $this->addSql('ALTER TABLE slot ADD CONSTRAINT FK_AC0E206738248176 FOREIGN KEY (currency_id) REFERENCES currency (id)');
        $this->addSql('ALTER TABLE slot ADD CONSTRAINT FK_AC0E2067FEEFC64B FOREIGN KEY (pitch_id) REFERENCES pitch (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE slot DROP FOREIGN KEY FK_AC0E206738248176');
        $this->addSql('ALTER TABLE slot DROP FOREIGN KEY FK_AC0E2067FEEFC64B');
        $this->addSql('ALTER TABLE pitch DROP FOREIGN KEY FK_279FBED9AC78BCF8');
        $this->addSql('DROP TABLE currency');
        $this->addSql('DROP TABLE pitch');
        $this->addSql('DROP TABLE slot');
        $this->addSql('DROP TABLE sport');
    }
}
