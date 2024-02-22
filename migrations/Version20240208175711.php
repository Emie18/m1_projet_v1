<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240208175711 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE stage DROP INDEX UNIQ_C27C936941EC5C6C, ADD INDEX IDX_C27C936941EC5C6C (tuteur_isen_id)');
        $this->addSql('ALTER TABLE stage DROP INDEX UNIQ_C27C9369681B4743, ADD INDEX IDX_C27C9369681B4743 (tuteur_stage_id)');
        $this->addSql('ALTER TABLE stage DROP INDEX UNIQ_C27C9369C5697D6D, ADD INDEX IDX_C27C9369C5697D6D (apprenant_id)');
        $this->addSql('ALTER TABLE stage DROP INDEX UNIQ_C27C9369A4AEAFEA, ADD INDEX IDX_C27C9369A4AEAFEA (entreprise_id)');
        $this->addSql('ALTER TABLE stage DROP INDEX UNIQ_C27C93697A45358C, ADD INDEX IDX_C27C93697A45358C (groupe_id)');
        $this->addSql('ALTER TABLE stage DROP INDEX UNIQ_C27C9369A59B3775, ADD INDEX IDX_C27C9369A59B3775 (soutenance_id)');
        $this->addSql('ALTER TABLE stage DROP INDEX UNIQ_C27C9369ED0D7993, ADD INDEX IDX_C27C9369ED0D7993 (eval_entreprise_id)');
        $this->addSql('ALTER TABLE stage DROP INDEX UNIQ_C27C93691DFBCC46, ADD INDEX IDX_C27C93691DFBCC46 (rapport_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE stage DROP INDEX IDX_C27C936941EC5C6C, ADD UNIQUE INDEX UNIQ_C27C936941EC5C6C (tuteur_isen_id)');
        $this->addSql('ALTER TABLE stage DROP INDEX IDX_C27C9369681B4743, ADD UNIQUE INDEX UNIQ_C27C9369681B4743 (tuteur_stage_id)');
        $this->addSql('ALTER TABLE stage DROP INDEX IDX_C27C9369C5697D6D, ADD UNIQUE INDEX UNIQ_C27C9369C5697D6D (apprenant_id)');
        $this->addSql('ALTER TABLE stage DROP INDEX IDX_C27C9369A4AEAFEA, ADD UNIQUE INDEX UNIQ_C27C9369A4AEAFEA (entreprise_id)');
        $this->addSql('ALTER TABLE stage DROP INDEX IDX_C27C93697A45358C, ADD UNIQUE INDEX UNIQ_C27C93697A45358C (groupe_id)');
        $this->addSql('ALTER TABLE stage DROP INDEX IDX_C27C9369A59B3775, ADD UNIQUE INDEX UNIQ_C27C9369A59B3775 (soutenance_id)');
        $this->addSql('ALTER TABLE stage DROP INDEX IDX_C27C9369ED0D7993, ADD UNIQUE INDEX UNIQ_C27C9369ED0D7993 (eval_entreprise_id)');
        $this->addSql('ALTER TABLE stage DROP INDEX IDX_C27C93691DFBCC46, ADD UNIQUE INDEX UNIQ_C27C93691DFBCC46 (rapport_id)');
    }
}
