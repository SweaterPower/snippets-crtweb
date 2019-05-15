<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Добавляет роли пользователей
 */
final class Version20190423062512 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'add user roles';
    }

    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('INSERT INTO user_role (code, title) VALUES ("user", "пользователь")');
        $this->addSql('INSERT INTO user_role (code, title) VALUES ("admin", "администратор")');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('DELETE FROM user_role');
    }
}
