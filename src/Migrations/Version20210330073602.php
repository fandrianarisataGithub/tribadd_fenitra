<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210330073602 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE person_participant (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE person_participant_event (person_participant_id INT NOT NULL, event_id INT NOT NULL, INDEX IDX_F3CAF3FB78C10027 (person_participant_id), INDEX IDX_F3CAF3FB71F7E88B (event_id), PRIMARY KEY(person_participant_id, event_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE person_participant_event ADD CONSTRAINT FK_F3CAF3FB78C10027 FOREIGN KEY (person_participant_id) REFERENCES person_participant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE person_participant_event ADD CONSTRAINT FK_F3CAF3FB71F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event ADD createur VARCHAR(255) DEFAULT NULL, CHANGE participant participant INT DEFAULT NULL, CHANGE created_at created_at DATETIME NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE person_participant_event DROP FOREIGN KEY FK_F3CAF3FB78C10027');
        $this->addSql('DROP TABLE person_participant');
        $this->addSql('DROP TABLE person_participant_event');
        $this->addSql('ALTER TABLE event DROP createur, CHANGE participant participant INT NOT NULL, CHANGE created_at created_at DATE DEFAULT NULL');
    }
}
