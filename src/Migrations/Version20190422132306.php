<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Добавляет статусы пользователя
 */
final class Version20190422132306 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'add user statuses';
    }

    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('INSERT INTO user_status (code, title) VALUES ("not_confirmed", "не подтвержден")');
        $this->addSql('INSERT INTO user_status (code, title) VALUES ("not_active", "не активен")');
        $this->addSql('INSERT INTO user_status (code, title) VALUES ("active", "активен")');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('DELETE FROM user_status');
    }
}
