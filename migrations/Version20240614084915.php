<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240614084915 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE log_conges ADD user_initiative_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE log_conges ADD CONSTRAINT FK_76DD8D9845F33A21 FOREIGN KEY (user_initiative_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_76DD8D9845F33A21 ON log_conges (user_initiative_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE log_conges DROP FOREIGN KEY FK_76DD8D9845F33A21');
        $this->addSql('DROP INDEX IDX_76DD8D9845F33A21 ON log_conges');
        $this->addSql('ALTER TABLE log_conges DROP user_initiative_id');
    }
}
