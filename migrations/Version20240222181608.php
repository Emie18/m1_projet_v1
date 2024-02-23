<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240222181608 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE apprenant (id INT AUTO_INCREMENT NOT NULL, num_apprenant INT NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE entreprise (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(50) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE etat (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE groupe (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE stage (id INT AUTO_INCREMENT NOT NULL, tuteur_isen_id INT DEFAULT NULL, tuteur_stage_id INT DEFAULT NULL, apprenant_id INT DEFAULT NULL, entreprise_id INT DEFAULT NULL, groupe_id INT DEFAULT NULL, soutenance_id INT DEFAULT NULL, eval_entreprise_id INT DEFAULT NULL, rapport_id INT DEFAULT NULL, titre VARCHAR(255) NOT NULL, date_debut DATE NOT NULL, date_fin DATE NOT NULL, description VARCHAR(255) NOT NULL, commentaire VARCHAR(255) DEFAULT NULL, INDEX IDX_C27C936941EC5C6C (tuteur_isen_id), INDEX IDX_C27C9369681B4743 (tuteur_stage_id), INDEX IDX_C27C9369C5697D6D (apprenant_id), INDEX IDX_C27C9369A4AEAFEA (entreprise_id), INDEX IDX_C27C93697A45358C (groupe_id), INDEX IDX_C27C9369A59B3775 (soutenance_id), INDEX IDX_C27C9369ED0D7993 (eval_entreprise_id), INDEX IDX_C27C93691DFBCC46 (rapport_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tuteur_isen (id INT AUTO_INCREMENT NOT NULL, num_tuteur_isen INT NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tuteur_stage (id INT AUTO_INCREMENT NOT NULL, num_tuteur_stage INT NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE stage ADD CONSTRAINT FK_C27C936941EC5C6C FOREIGN KEY (tuteur_isen_id) REFERENCES tuteur_isen (id)');
        $this->addSql('ALTER TABLE stage ADD CONSTRAINT FK_C27C9369681B4743 FOREIGN KEY (tuteur_stage_id) REFERENCES tuteur_stage (id)');
        $this->addSql('ALTER TABLE stage ADD CONSTRAINT FK_C27C9369C5697D6D FOREIGN KEY (apprenant_id) REFERENCES apprenant (id)');
        $this->addSql('ALTER TABLE stage ADD CONSTRAINT FK_C27C9369A4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES entreprise (id)');
        $this->addSql('ALTER TABLE stage ADD CONSTRAINT FK_C27C93697A45358C FOREIGN KEY (groupe_id) REFERENCES groupe (id)');
        $this->addSql('ALTER TABLE stage ADD CONSTRAINT FK_C27C9369A59B3775 FOREIGN KEY (soutenance_id) REFERENCES etat (id)');
        $this->addSql('ALTER TABLE stage ADD CONSTRAINT FK_C27C9369ED0D7993 FOREIGN KEY (eval_entreprise_id) REFERENCES etat (id)');
        $this->addSql('ALTER TABLE stage ADD CONSTRAINT FK_C27C93691DFBCC46 FOREIGN KEY (rapport_id) REFERENCES etat (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE stage DROP FOREIGN KEY FK_C27C936941EC5C6C');
        $this->addSql('ALTER TABLE stage DROP FOREIGN KEY FK_C27C9369681B4743');
        $this->addSql('ALTER TABLE stage DROP FOREIGN KEY FK_C27C9369C5697D6D');
        $this->addSql('ALTER TABLE stage DROP FOREIGN KEY FK_C27C9369A4AEAFEA');
        $this->addSql('ALTER TABLE stage DROP FOREIGN KEY FK_C27C93697A45358C');
        $this->addSql('ALTER TABLE stage DROP FOREIGN KEY FK_C27C9369A59B3775');
        $this->addSql('ALTER TABLE stage DROP FOREIGN KEY FK_C27C9369ED0D7993');
        $this->addSql('ALTER TABLE stage DROP FOREIGN KEY FK_C27C93691DFBCC46');
        $this->addSql('DROP TABLE apprenant');
        $this->addSql('DROP TABLE entreprise');
        $this->addSql('DROP TABLE etat');
        $this->addSql('DROP TABLE groupe');
        $this->addSql('DROP TABLE stage');
        $this->addSql('DROP TABLE tuteur_isen');
        $this->addSql('DROP TABLE tuteur_stage');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
