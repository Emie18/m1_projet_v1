<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240208175505 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE stage DROP INDEX UNIQ_C27C936941EC5C6C, ADD INDEX IDX_C27C936941EC5C6C (tuteur_isen_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE stage DROP INDEX IDX_C27C936941EC5C6C, ADD UNIQUE INDEX UNIQ_C27C936941EC5C6C (tuteur_isen_id)');
    }
}
