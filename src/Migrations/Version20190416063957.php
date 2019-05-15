<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Добавляет таблицу сниппетов
 */
final class Version20190416063957 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'add snippets table';
    }

    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE snippet (id INT AUTO_INCREMENT NOT NULL, access_type_id INT NOT NULL, title VARCHAR(255) NOT NULL, text VARCHAR(255) NOT NULL, url_code VARCHAR(255) NOT NULL, INDEX IDX_961C8CD5D695686 (access_type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE snippet ADD CONSTRAINT FK_961C8CD5D695686 FOREIGN KEY (access_type_id) REFERENCES access_type (id)');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE snippet');
    }
}
