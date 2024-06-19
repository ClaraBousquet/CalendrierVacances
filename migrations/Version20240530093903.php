<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240530093903 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE type_user (type_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_5A9C1341C54C8C93 (type_id), INDEX IDX_5A9C1341A76ED395 (user_id), PRIMARY KEY(type_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE type_user ADD CONSTRAINT FK_5A9C1341C54C8C93 FOREIGN KEY (type_id) REFERENCES type (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE type_user ADD CONSTRAINT FK_5A9C1341A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE type ADD is_restricted_user TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE type_user DROP FOREIGN KEY FK_5A9C1341C54C8C93');
        $this->addSql('ALTER TABLE type_user DROP FOREIGN KEY FK_5A9C1341A76ED395');
        $this->addSql('DROP TABLE type_user');
        $this->addSql('ALTER TABLE type DROP is_restricted_user');
    }
}
