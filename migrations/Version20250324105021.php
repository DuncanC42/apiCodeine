<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250324105021 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE leaderboard_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE parametres_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE score_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE statistiques_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE token_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE leaderboard (id INT NOT NULL, scorescore_global INT NOT NULL, date_maj TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE parametres (id INT NOT NULL, date_cloture TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, date_debut TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE score (id INT NOT NULL, jeu_id INT NOT NULL, points INT NOT NULL, temps_jeu INT NOT NULL, nb_essais INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_329937518C9E392E ON score (jeu_id)');
        $this->addSql('CREATE TABLE statistiques (id INT NOT NULL, nb_joueurs_total INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE token (id INT NOT NULL, key VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN token.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE score ADD CONSTRAINT FK_329937518C9E392E FOREIGN KEY (jeu_id) REFERENCES jeu (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE jeu ALTER description SET NOT NULL');
        $this->addSql('ALTER TABLE jeu ALTER regles SET NOT NULL');
        $this->addSql('ALTER TABLE jeu ALTER message_fin SET NOT NULL');
        $this->addSql('ALTER TABLE jeu ALTER photo SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE leaderboard_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE parametres_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE score_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE statistiques_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE token_id_seq CASCADE');
        $this->addSql('DROP TABLE leaderboard');
        $this->addSql('DROP TABLE parametres');
        $this->addSql('DROP TABLE score');
        $this->addSql('DROP TABLE statistiques');
        $this->addSql('DROP TABLE token');
        $this->addSql('ALTER TABLE jeu ALTER description DROP NOT NULL');
        $this->addSql('ALTER TABLE jeu ALTER regles DROP NOT NULL');
        $this->addSql('ALTER TABLE jeu ALTER message_fin DROP NOT NULL');
        $this->addSql('ALTER TABLE jeu ALTER photo DROP NOT NULL');
    }
}
