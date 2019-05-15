<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Добавляет таблицу пользователя и связь пользователя со сниппетом
 */
final class Version20190422081830 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'add user table and snippet-user relation';
    }

    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, status_id INT NOT NULL, email VARCHAR(180) NOT NULL, password VARCHAR(255), username VARCHAR(255) NOT NULL, email_request_token VARCHAR(255) NOT NULL, email_request_datetime DATETIME NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), INDEX IDX_8D93D6496BF700BD (status_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6496BF700BD FOREIGN KEY (status_id) REFERENCES user_status (id)');
        $this->addSql('ALTER TABLE snippet ADD owner INT NOT NULL');
        $this->addSql('ALTER TABLE snippet ADD CONSTRAINT FK_961C8CD5A76ED395 FOREIGN KEY (owner) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_961C8CD5A76ED395 ON snippet (owner)');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE snippet DROP FOREIGN KEY FK_961C8CD5A76ED395');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP INDEX IDX_961C8CD5A76ED395 ON snippet');
        $this->addSql('ALTER TABLE snippet DROP owner');
    }
}
