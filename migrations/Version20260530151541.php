<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260530151541 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE annonce ADD CONSTRAINT FK_F65593E58B419309 FOREIGN KEY (colocation_id) REFERENCES colocation (id)');
        $this->addSql('ALTER TABLE avis_annonce ADD CONSTRAINT FK_DD5603E38805AB2F FOREIGN KEY (annonce_id) REFERENCES annonce (id)');
        $this->addSql('ALTER TABLE avis_annonce ADD CONSTRAINT FK_DD5603E360BB6FE6 FOREIGN KEY (auteur_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE chambre ADD CONSTRAINT FK_C509E4FF8B419309 FOREIGN KEY (colocation_id) REFERENCES colocation (id)');
        $this->addSql('ALTER TABLE chambre ADD CONSTRAINT FK_C509E4FFD8A38199 FOREIGN KEY (locataire_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE charge ADD CONSTRAINT FK_556BA4348B419309 FOREIGN KEY (colocation_id) REFERENCES colocation (id)');
        $this->addSql('ALTER TABLE colocation ADD CONSTRAINT FK_613CCFD276C50E4A FOREIGN KEY (proprietaire_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE evaluation_locataire ADD CONSTRAINT FK_167C3D6FD8A38199 FOREIGN KEY (locataire_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE evaluation_locataire ADD CONSTRAINT FK_167C3D6F76C50E4A FOREIGN KEY (proprietaire_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE evaluation_locataire ADD CONSTRAINT FK_167C3D6F8B419309 FOREIGN KEY (colocation_id) REFERENCES colocation (id)');
        $this->addSql('ALTER TABLE loyer ADD CONSTRAINT FK_40456298B419309 FOREIGN KEY (colocation_id) REFERENCES colocation (id)');
        $this->addSql('ALTER TABLE loyer ADD CONSTRAINT FK_40456299B177F54 FOREIGN KEY (chambre_id) REFERENCES chambre (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F10335F61 FOREIGN KEY (expediteur_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FA4F84F6E FOREIGN KEY (destinataire_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F8B419309 FOREIGN KEY (colocation_id) REFERENCES colocation (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE photo_annonce ADD CONSTRAINT FK_C3B584688805AB2F FOREIGN KEY (annonce_id) REFERENCES annonce (id)');
        $this->addSql('ALTER TABLE quittance ADD CONSTRAINT FK_D57587DDBA518690 FOREIGN KEY (loyer_id) REFERENCES loyer (id)');
        $this->addSql('ALTER TABLE semainier ADD CONSTRAINT FK_30B95EEAD2235D39 FOREIGN KEY (tache_id) REFERENCES tache (id)');
        $this->addSql('ALTER TABLE tache ADD CONSTRAINT FK_938720758B419309 FOREIGN KEY (colocation_id) REFERENCES colocation (id)');
        $this->addSql('ALTER TABLE tache ADD CONSTRAINT FK_938720758E7B8AB0 FOREIGN KEY (assigne_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE tantieme ADD CONSTRAINT FK_BBEC1E089B177F54 FOREIGN KEY (chambre_id) REFERENCES chambre (id)');
        $this->addSql('ALTER TABLE tantieme ADD CONSTRAINT FK_BBEC1E0855284914 FOREIGN KEY (charge_id) REFERENCES charge (id)');
        $this->addSql('ALTER TABLE visite_annonce ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE visite_annonce ADD CONSTRAINT FK_3C54E6C38805AB2F FOREIGN KEY (annonce_id) REFERENCES annonce (id)');
        $this->addSql('ALTER TABLE visite_annonce ADD CONSTRAINT FK_3C54E6C3A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_3C54E6C3A76ED395 ON visite_annonce (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE annonce DROP FOREIGN KEY FK_F65593E58B419309');
        $this->addSql('ALTER TABLE avis_annonce DROP FOREIGN KEY FK_DD5603E38805AB2F');
        $this->addSql('ALTER TABLE avis_annonce DROP FOREIGN KEY FK_DD5603E360BB6FE6');
        $this->addSql('ALTER TABLE chambre DROP FOREIGN KEY FK_C509E4FF8B419309');
        $this->addSql('ALTER TABLE chambre DROP FOREIGN KEY FK_C509E4FFD8A38199');
        $this->addSql('ALTER TABLE charge DROP FOREIGN KEY FK_556BA4348B419309');
        $this->addSql('ALTER TABLE colocation DROP FOREIGN KEY FK_613CCFD276C50E4A');
        $this->addSql('ALTER TABLE evaluation_locataire DROP FOREIGN KEY FK_167C3D6FD8A38199');
        $this->addSql('ALTER TABLE evaluation_locataire DROP FOREIGN KEY FK_167C3D6F76C50E4A');
        $this->addSql('ALTER TABLE evaluation_locataire DROP FOREIGN KEY FK_167C3D6F8B419309');
        $this->addSql('ALTER TABLE loyer DROP FOREIGN KEY FK_40456298B419309');
        $this->addSql('ALTER TABLE loyer DROP FOREIGN KEY FK_40456299B177F54');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F10335F61');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FA4F84F6E');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F8B419309');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAA76ED395');
        $this->addSql('ALTER TABLE photo_annonce DROP FOREIGN KEY FK_C3B584688805AB2F');
        $this->addSql('ALTER TABLE quittance DROP FOREIGN KEY FK_D57587DDBA518690');
        $this->addSql('ALTER TABLE semainier DROP FOREIGN KEY FK_30B95EEAD2235D39');
        $this->addSql('ALTER TABLE tache DROP FOREIGN KEY FK_938720758B419309');
        $this->addSql('ALTER TABLE tache DROP FOREIGN KEY FK_938720758E7B8AB0');
        $this->addSql('ALTER TABLE tantieme DROP FOREIGN KEY FK_BBEC1E089B177F54');
        $this->addSql('ALTER TABLE tantieme DROP FOREIGN KEY FK_BBEC1E0855284914');
        $this->addSql('ALTER TABLE visite_annonce DROP FOREIGN KEY FK_3C54E6C38805AB2F');
        $this->addSql('ALTER TABLE visite_annonce DROP FOREIGN KEY FK_3C54E6C3A76ED395');
        $this->addSql('DROP INDEX IDX_3C54E6C3A76ED395 ON visite_annonce');
        $this->addSql('ALTER TABLE visite_annonce DROP user_id');
    }
}
