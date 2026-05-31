<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260531000002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create candidature table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('SET FOREIGN_KEY_CHECKS = 0');
        $this->addSql('CREATE TABLE candidature (
            id INT AUTO_INCREMENT NOT NULL,
            locataire_id INT NOT NULL,
            annonce_id INT NOT NULL,
            statut VARCHAR(20) NOT NULL DEFAULT \'en_attente\',
            cree_le DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_cand_loc (locataire_id),
            INDEX IDX_cand_ann (annonce_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        // FK omises — bug InnoDB WAMP/Windows : FK créées avec FOREIGN_KEY_CHECKS=0 échouent aux INSERT
        $this->addSql('SET FOREIGN_KEY_CHECKS = 1');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('SET FOREIGN_KEY_CHECKS = 0');
        $this->addSql('DROP TABLE candidature');
        $this->addSql('SET FOREIGN_KEY_CHECKS = 1');
    }
}
