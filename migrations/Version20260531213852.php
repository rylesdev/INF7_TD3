<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260531213852 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE annonce (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, prix NUMERIC(10, 2) NOT NULL, localisation VARCHAR(255) NOT NULL, statut VARCHAR(20) NOT NULL, meta_description VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, colocation_id INT NOT NULL, INDEX IDX_F65593E58B419309 (colocation_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE avis_annonce (id INT AUTO_INCREMENT NOT NULL, note INT NOT NULL, commentaire LONGTEXT DEFAULT NULL, cree_le DATETIME NOT NULL, annonce_id INT NOT NULL, auteur_id INT NOT NULL, INDEX IDX_DD5603E38805AB2F (annonce_id), INDEX IDX_DD5603E360BB6FE6 (auteur_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE candidature (id INT AUTO_INCREMENT NOT NULL, statut VARCHAR(20) NOT NULL, piece_identite VARCHAR(255) DEFAULT NULL, justificatif_revenu VARCHAR(255) DEFAULT NULL, cree_le DATETIME NOT NULL, locataire_id INT NOT NULL, annonce_id INT NOT NULL, INDEX IDX_E33BD3B8D8A38199 (locataire_id), INDEX IDX_E33BD3B88805AB2F (annonce_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE chambre (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, surface NUMERIC(6, 2) NOT NULL, loyer_mensuel NUMERIC(10, 2) DEFAULT NULL, colocation_id INT NOT NULL, locataire_id INT DEFAULT NULL, INDEX IDX_C509E4FF8B419309 (colocation_id), INDEX IDX_C509E4FFD8A38199 (locataire_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE charge (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(50) NOT NULL, montant NUMERIC(10, 2) NOT NULL, date DATE NOT NULL, mois VARCHAR(20) DEFAULT NULL, annee INT DEFAULT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, colocation_id INT NOT NULL, INDEX IDX_556BA4348B419309 (colocation_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE colocation (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, adresse VARCHAR(255) NOT NULL, ville VARCHAR(100) NOT NULL, code_postal VARCHAR(10) NOT NULL, description LONGTEXT DEFAULT NULL, loyer NUMERIC(10, 2) DEFAULT NULL, latitude NUMERIC(8, 6) DEFAULT NULL, longitude NUMERIC(9, 6) DEFAULT NULL, created_at DATETIME NOT NULL, proprietaire_id INT NOT NULL, INDEX IDX_613CCFD276C50E4A (proprietaire_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE evaluation_locataire (id INT AUTO_INCREMENT NOT NULL, note INT NOT NULL, commentaire LONGTEXT DEFAULT NULL, cree_le DATETIME NOT NULL, locataire_id INT NOT NULL, proprietaire_id INT NOT NULL, colocation_id INT NOT NULL, INDEX IDX_167C3D6FD8A38199 (locataire_id), INDEX IDX_167C3D6F76C50E4A (proprietaire_id), INDEX IDX_167C3D6F8B419309 (colocation_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE evaluation_proprietaire (id INT AUTO_INCREMENT NOT NULL, note INT NOT NULL, commentaire LONGTEXT DEFAULT NULL, cree_le DATETIME NOT NULL, locataire_id INT NOT NULL, proprietaire_id INT NOT NULL, colocation_id INT NOT NULL, INDEX IDX_A7D124DDD8A38199 (locataire_id), INDEX IDX_A7D124DD76C50E4A (proprietaire_id), INDEX IDX_A7D124DD8B419309 (colocation_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE loyer (id INT AUTO_INCREMENT NOT NULL, montant NUMERIC(10, 2) NOT NULL, date_echeance DATE NOT NULL, date_paiement DATE DEFAULT NULL, statut VARCHAR(20) NOT NULL, mois VARCHAR(20) NOT NULL, annee INT NOT NULL, created_at DATETIME NOT NULL, colocation_id INT NOT NULL, chambre_id INT DEFAULT NULL, INDEX IDX_40456298B419309 (colocation_id), INDEX IDX_40456299B177F54 (chambre_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE message (id INT AUTO_INCREMENT NOT NULL, contenu LONGTEXT NOT NULL, lu TINYINT NOT NULL, automatique TINYINT NOT NULL, lien VARCHAR(255) DEFAULT NULL, envoye_le DATETIME NOT NULL, expediteur_id INT NOT NULL, destinataire_id INT NOT NULL, colocation_id INT NOT NULL, INDEX IDX_B6BD307F10335F61 (expediteur_id), INDEX IDX_B6BD307FA4F84F6E (destinataire_id), INDEX IDX_B6BD307F8B419309 (colocation_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(50) NOT NULL, titre VARCHAR(255) NOT NULL, message LONGTEXT DEFAULT NULL, lue TINYINT NOT NULL, lien VARCHAR(255) DEFAULT NULL, cree_le DATETIME NOT NULL, user_id INT NOT NULL, INDEX IDX_BF5476CAA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE photo_annonce (id INT AUTO_INCREMENT NOT NULL, filename VARCHAR(255) NOT NULL, alt VARCHAR(255) DEFAULT NULL, position INT NOT NULL, annonce_id INT NOT NULL, INDEX IDX_C3B584688805AB2F (annonce_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE quittance (id INT AUTO_INCREMENT NOT NULL, montant_loyer NUMERIC(10, 2) NOT NULL, montant_charges NUMERIC(10, 2) NOT NULL, montant_total NUMERIC(10, 2) NOT NULL, periode_debut DATE NOT NULL, periode_fin DATE NOT NULL, generee_at DATETIME NOT NULL, pdf_path VARCHAR(255) DEFAULT NULL, loyer_id INT NOT NULL, UNIQUE INDEX UNIQ_D57587DDBA518690 (loyer_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE semainier (id INT AUTO_INCREMENT NOT NULL, jour_semaine INT NOT NULL, date_debut DATE NOT NULL, date_fin DATE NOT NULL, created_at DATETIME NOT NULL, tache_id INT NOT NULL, UNIQUE INDEX UNIQ_30B95EEAD2235D39 (tache_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tache (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, type VARCHAR(50) NOT NULL, statut VARCHAR(20) NOT NULL, date_echeance DATE DEFAULT NULL, created_at DATETIME NOT NULL, colocation_id INT NOT NULL, assigne_id INT DEFAULT NULL, INDEX IDX_938720758B419309 (colocation_id), INDEX IDX_938720758E7B8AB0 (assigne_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tantieme (id INT AUTO_INCREMENT NOT NULL, pourcentage NUMERIC(5, 2) NOT NULL, montant_du NUMERIC(10, 2) NOT NULL, calcule_le DATETIME NOT NULL, chambre_id INT NOT NULL, charge_id INT NOT NULL, INDEX IDX_BBEC1E089B177F54 (chambre_id), INDEX IDX_BBEC1E0855284914 (charge_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, prenom VARCHAR(100) NOT NULL, nom VARCHAR(100) NOT NULL, telephone VARCHAR(20) DEFAULT NULL, photo_profil VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, reset_token VARCHAR(100) DEFAULT NULL, reset_token_expires_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE visite_annonce (id INT AUTO_INCREMENT NOT NULL, ip_address VARCHAR(45) DEFAULT NULL, visite_le DATETIME NOT NULL, annonce_id INT NOT NULL, user_id INT DEFAULT NULL, INDEX IDX_3C54E6C38805AB2F (annonce_id), INDEX IDX_3C54E6C3A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE annonce ADD CONSTRAINT FK_F65593E58B419309 FOREIGN KEY (colocation_id) REFERENCES colocation (id)');
        $this->addSql('ALTER TABLE avis_annonce ADD CONSTRAINT FK_DD5603E38805AB2F FOREIGN KEY (annonce_id) REFERENCES annonce (id)');
        $this->addSql('ALTER TABLE avis_annonce ADD CONSTRAINT FK_DD5603E360BB6FE6 FOREIGN KEY (auteur_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE candidature ADD CONSTRAINT FK_E33BD3B8D8A38199 FOREIGN KEY (locataire_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE candidature ADD CONSTRAINT FK_E33BD3B88805AB2F FOREIGN KEY (annonce_id) REFERENCES annonce (id)');
        $this->addSql('ALTER TABLE chambre ADD CONSTRAINT FK_C509E4FF8B419309 FOREIGN KEY (colocation_id) REFERENCES colocation (id)');
        $this->addSql('ALTER TABLE chambre ADD CONSTRAINT FK_C509E4FFD8A38199 FOREIGN KEY (locataire_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE charge ADD CONSTRAINT FK_556BA4348B419309 FOREIGN KEY (colocation_id) REFERENCES colocation (id)');
        $this->addSql('ALTER TABLE colocation ADD CONSTRAINT FK_613CCFD276C50E4A FOREIGN KEY (proprietaire_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE evaluation_locataire ADD CONSTRAINT FK_167C3D6FD8A38199 FOREIGN KEY (locataire_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE evaluation_locataire ADD CONSTRAINT FK_167C3D6F76C50E4A FOREIGN KEY (proprietaire_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE evaluation_locataire ADD CONSTRAINT FK_167C3D6F8B419309 FOREIGN KEY (colocation_id) REFERENCES colocation (id)');
        $this->addSql('ALTER TABLE evaluation_proprietaire ADD CONSTRAINT FK_A7D124DDD8A38199 FOREIGN KEY (locataire_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE evaluation_proprietaire ADD CONSTRAINT FK_A7D124DD76C50E4A FOREIGN KEY (proprietaire_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE evaluation_proprietaire ADD CONSTRAINT FK_A7D124DD8B419309 FOREIGN KEY (colocation_id) REFERENCES colocation (id)');
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
        $this->addSql('ALTER TABLE visite_annonce ADD CONSTRAINT FK_3C54E6C38805AB2F FOREIGN KEY (annonce_id) REFERENCES annonce (id)');
        $this->addSql('ALTER TABLE visite_annonce ADD CONSTRAINT FK_3C54E6C3A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE annonce DROP FOREIGN KEY FK_F65593E58B419309');
        $this->addSql('ALTER TABLE avis_annonce DROP FOREIGN KEY FK_DD5603E38805AB2F');
        $this->addSql('ALTER TABLE avis_annonce DROP FOREIGN KEY FK_DD5603E360BB6FE6');
        $this->addSql('ALTER TABLE candidature DROP FOREIGN KEY FK_E33BD3B8D8A38199');
        $this->addSql('ALTER TABLE candidature DROP FOREIGN KEY FK_E33BD3B88805AB2F');
        $this->addSql('ALTER TABLE chambre DROP FOREIGN KEY FK_C509E4FF8B419309');
        $this->addSql('ALTER TABLE chambre DROP FOREIGN KEY FK_C509E4FFD8A38199');
        $this->addSql('ALTER TABLE charge DROP FOREIGN KEY FK_556BA4348B419309');
        $this->addSql('ALTER TABLE colocation DROP FOREIGN KEY FK_613CCFD276C50E4A');
        $this->addSql('ALTER TABLE evaluation_locataire DROP FOREIGN KEY FK_167C3D6FD8A38199');
        $this->addSql('ALTER TABLE evaluation_locataire DROP FOREIGN KEY FK_167C3D6F76C50E4A');
        $this->addSql('ALTER TABLE evaluation_locataire DROP FOREIGN KEY FK_167C3D6F8B419309');
        $this->addSql('ALTER TABLE evaluation_proprietaire DROP FOREIGN KEY FK_A7D124DDD8A38199');
        $this->addSql('ALTER TABLE evaluation_proprietaire DROP FOREIGN KEY FK_A7D124DD76C50E4A');
        $this->addSql('ALTER TABLE evaluation_proprietaire DROP FOREIGN KEY FK_A7D124DD8B419309');
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
        $this->addSql('DROP TABLE annonce');
        $this->addSql('DROP TABLE avis_annonce');
        $this->addSql('DROP TABLE candidature');
        $this->addSql('DROP TABLE chambre');
        $this->addSql('DROP TABLE charge');
        $this->addSql('DROP TABLE colocation');
        $this->addSql('DROP TABLE evaluation_locataire');
        $this->addSql('DROP TABLE evaluation_proprietaire');
        $this->addSql('DROP TABLE loyer');
        $this->addSql('DROP TABLE message');
        $this->addSql('DROP TABLE notification');
        $this->addSql('DROP TABLE photo_annonce');
        $this->addSql('DROP TABLE quittance');
        $this->addSql('DROP TABLE semainier');
        $this->addSql('DROP TABLE tache');
        $this->addSql('DROP TABLE tantieme');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE visite_annonce');
    }
}
