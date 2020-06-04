<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Infrastructure\Persistence\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200302115055 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Very first migration. Yay!';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE checkout (id VARCHAR(36) NOT NULL, agreed TINYINT(1) NOT NULL, shift_id VARCHAR(255) NOT NULL, started_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', end DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', jobseeker_id VARCHAR(255) NOT NULL, client_id VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE proposal (id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', checkout_id VARCHAR(36) DEFAULT NULL, worked_from DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', worked_until DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', minutes_break INT NOT NULL, compensation VARCHAR(255) NOT NULL, proposed_by VARCHAR(255) NOT NULL, proposed_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_BFE59472146D8724 (checkout_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE proposal ADD CONSTRAINT FK_BFE59472146D8724 FOREIGN KEY (checkout_id) REFERENCES checkout (id)');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE proposal DROP FOREIGN KEY FK_BFE59472146D8724');
        $this->addSql('DROP TABLE checkout');
        $this->addSql('DROP TABLE proposal');
    }
}
