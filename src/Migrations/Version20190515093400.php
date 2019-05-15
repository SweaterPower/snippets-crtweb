<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Добавляет флаг, указывающий, является ли сниппет приватным
 */
final class Version20190515093400 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'add private flag for snippet';
    }

    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE snippet ADD is_private TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE snippet RENAME INDEX idx_961c8cd5a76ed395 TO IDX_961C8CD57E3C61F9');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE snippet DROP is_private');
        $this->addSql('ALTER TABLE snippet RENAME INDEX idx_961c8cd57e3c61f9 TO IDX_961C8CD5A76ED395');
    }
}
