<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251105164104 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE animals (id SERIAL NOT NULL, owner_id INT NOT NULL, name VARCHAR(2255) NOT NULL, birth_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, weight DOUBLE PRECISION NOT NULL, color VARCHAR(30) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, type VARCHAR(50) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_animal_name ON animals (name)');
        $this->addSql('CREATE INDEX idx_animal_type ON animals (type)');
        $this->addSql('CREATE INDEX idx_animal_owner ON animals (owner_id)');
        $this->addSql('CREATE INDEX idx_animal_birth_date ON animals (birth_date)');
        $this->addSql('CREATE INDEX idx_animal_created_at ON animals (created_at)');
        $this->addSql('COMMENT ON COLUMN animals.name IS \'Nom de l\'\'animal\'');
        $this->addSql('COMMENT ON COLUMN animals.birth_date IS \'Date de naissance(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN animals.weight IS \'Poids en kilogrammes\'');
        $this->addSql('COMMENT ON COLUMN animals.color IS \'Couleur de l\'\'animal\'');
        $this->addSql('COMMENT ON COLUMN animals.created_at IS \'Date de création(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN animals.updated_at IS \'Date de mise à jour(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE owners (id SERIAL NOT NULL, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, email VARCHAR(255) NOT NULL, phone_number VARCHAR(30) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, registration_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, is_active BOOLEAN DEFAULT true NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_owner_last_name ON owners (last_name)');
        $this->addSql('CREATE INDEX idx_owner_email ON owners (email)');
        $this->addSql('CREATE INDEX idx_owner_registration_date ON owners (registration_date)');
        $this->addSql('CREATE INDEX idx_owner_is_active ON owners (is_active)');
        $this->addSql('CREATE INDEX idx_owner_full_name ON owners (last_name, first_name)');
        $this->addSql('CREATE UNIQUE INDEX uniq_owner_email ON owners (email)');
        $this->addSql('COMMENT ON COLUMN owners.first_name IS \'Prénom du propriétaire\'');
        $this->addSql('COMMENT ON COLUMN owners.last_name IS \'Nom de famille du propriétaire\'');
        $this->addSql('COMMENT ON COLUMN owners.email IS \'Email unique du propriétaire\'');
        $this->addSql('COMMENT ON COLUMN owners.phone_number IS \'Numéro de téléphone du propriétaire\'');
        $this->addSql('COMMENT ON COLUMN owners.address IS \'Adresse postale du propriétaire\'');
        $this->addSql('COMMENT ON COLUMN owners.registration_date IS \'Date d\'\'inscription du propriétaire(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN owners.updated_at IS \'Date de dernière mise à jour(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN owners.is_active IS \'Statut actif du propriétaire\'');
        $this->addSql('CREATE TABLE messenger_messages (id BIGSERIAL NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
        $this->addSql('COMMENT ON COLUMN messenger_messages.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.available_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.delivered_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE OR REPLACE FUNCTION notify_messenger_messages() RETURNS TRIGGER AS $$
            BEGIN
                PERFORM pg_notify(\'messenger_messages\', NEW.queue_name::text);
                RETURN NEW;
            END;
        $$ LANGUAGE plpgsql;');
        $this->addSql('DROP TRIGGER IF EXISTS notify_trigger ON messenger_messages;');
        $this->addSql('CREATE TRIGGER notify_trigger AFTER INSERT OR UPDATE ON messenger_messages FOR EACH ROW EXECUTE PROCEDURE notify_messenger_messages();');
        $this->addSql('ALTER TABLE animals ADD CONSTRAINT FK_966C69DD7E3C61F9 FOREIGN KEY (owner_id) REFERENCES owners (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE animals DROP CONSTRAINT FK_966C69DD7E3C61F9');
        $this->addSql('DROP TABLE animals');
        $this->addSql('DROP TABLE owners');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
