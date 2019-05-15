<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Добавляет уровни доступа к сниппету
 */
final class Version20190514080245 extends AbstractMigration
{

    public function getDescription(): string
    {
        return 'add access types';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('INSERT INTO access_type (code, title) VALUES ("private", "Приватный")');
        $this->addSql('INSERT INTO access_type (code, title) VALUES ("public", "Публичный")');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DELETE FROM access_type');
    }

}
