<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260531000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create evaluation_proprietaire table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('SET FOREIGN_KEY_CHECKS = 0');
        $this->addSql('CREATE TABLE evaluation_proprietaire (
            id INT AUTO_INCREMENT NOT NULL,
            locataire_id INT NOT NULL,
            proprietaire_id INT NOT NULL,
            colocation_id INT NOT NULL,
            note INT NOT NULL,
            commentaire LONGTEXT DEFAULT NULL,
            cree_le DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_eval_pro_loc (locataire_id),
            INDEX IDX_eval_pro_pro (proprietaire_id),
            INDEX IDX_eval_pro_col (colocation_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE evaluation_proprietaire
            ADD CONSTRAINT FK_eval_pro_loc FOREIGN KEY (locataire_id) REFERENCES `user` (id),
            ADD CONSTRAINT FK_eval_pro_pro FOREIGN KEY (proprietaire_id) REFERENCES `user` (id),
            ADD CONSTRAINT FK_eval_pro_col FOREIGN KEY (colocation_id) REFERENCES colocation (id)');
        $this->addSql('SET FOREIGN_KEY_CHECKS = 1');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE evaluation_proprietaire DROP FOREIGN KEY FK_eval_pro_loc');
        $this->addSql('ALTER TABLE evaluation_proprietaire DROP FOREIGN KEY FK_eval_pro_pro');
        $this->addSql('ALTER TABLE evaluation_proprietaire DROP FOREIGN KEY FK_eval_pro_col');
        $this->addSql('DROP TABLE evaluation_proprietaire');
    }
}
