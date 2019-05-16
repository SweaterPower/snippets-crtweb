<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Добавляет индексы для адреса сниппета и имени пользователя
 */
final class Version20190516091751 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'add snippet code and username indexes';
    }

    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE INDEX url_idx ON snippet (url_code)');
        $this->addSql('CREATE INDEX username_idx ON user (username)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649F85E0677 ON user (username)');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX url_idx ON snippet');
        $this->addSql('DROP INDEX username_idx ON user');
        $this->addSql('DROP INDEX UNIQ_8D93D649F85E0677 ON user');
    }
}
