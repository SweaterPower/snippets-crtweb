<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Добавляет запись о ключе доступа для пользователя и добавляет роль API-пользователя
 */
final class Version20190610093345 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'add api_token for user and user_role ROLE_API';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user ADD api_token VARCHAR(255) DEFAULT NULL');
        $this->addSql('INSERT INTO user_role (code, title) VALUES ("ROLE_API", "интерфейс")');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user DROP api_token');
        $this->addSql('DELETE FROM user_role (code, title) VALUES ("ROLE_API", "интерфейс")');
    }
}
