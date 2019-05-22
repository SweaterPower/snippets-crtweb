<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Добавляет индекс для адреса сниппета
 */
final class Version20190516091751 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'add snippet code index';
    }

    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE INDEX url_idx ON snippet (url_code)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649F85E0677 ON user (username)');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX url_idx ON snippet');
        $this->addSql('DROP INDEX UNIQ_8D93D649F85E0677 ON user');
    }
}
